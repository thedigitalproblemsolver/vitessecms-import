<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use VitesseCms\Content\Controllers\AdminitemController;
use VitesseCms\Content\Factories\ItemFactory;
use VitesseCms\Content\Models\Item;
use VitesseCms\Core\Services\UrlService;
use VitesseCms\Database\Models\FindValue;
use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Database\Utils\MongoUtil;
use VitesseCms\Datagroup\Models\DatagroupIterator;
use VitesseCms\Import\Helpers\ImportLineEventVehicle;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Repositories\RepositoryInterface;
use VitesseCms\Language\Models\Language;
use VitesseCms\Log\Services\LogService;

class ImportLineHandlerListener
{
    /**
     * @var RepositoryInterface
     */
    protected $repositories;

    /**
     * @var Manager
     */
    protected $eventsManager;

    /**
     * @var bool
     * @deprecated find another way to parse this
     */
    protected $parseUpdateOnly;

    /**
     * @var LogService
     */
    protected $logService;

    /**
     * @var UrlService
     */
    protected $urlService;

    public function __construct(
        RepositoryInterface $repositories,
        Manager $eventsManager,
        LogService $logService,
        UrlService $urlService
    ){
        $this->repositories = $repositories;
        $this->eventsManager = $eventsManager;
        $this->parseUpdateOnly = true;
        $this->logService = $logService;
        $this->urlService = $urlService;
    }

    protected function parseLine(Event $event, ImportLineEventVehicle $importLineEventHelper): void
    {
        $datagroup = $importLineEventHelper->datagroup;
        $importType = $importLineEventHelper->importType;
        $data = $importLineEventHelper->data;
        $header = $importLineEventHelper->header;
        $language = $importLineEventHelper->language;
        $uniqueFields = $importLineEventHelper->uniqueFields;
        $headerNameField = $importLineEventHelper->headerNameField;
        $fieldsToParse = $importLineEventHelper->fieldsToParse;

        $parentItem = $this->getItemFromDatagroupPath(
            $this->repositories->datagroup->getPathFromRoot($datagroup),
            $importType,
            $data,
            $header,
            $language
        );

        if ($parentItem === null):
            echo 'No parent item is found';
            die();
        endif;

        $item = $this->getBaseItem(
            $uniqueFields,
            $language,
            $data,
            $header,
            $importType,
            $parentItem,
            $headerNameField
        );

        $item = $this->parseFields(
            $fieldsToParse,
            $this->parseUpdateOnly,
            $data,
            $header,
            $item,
            $language
        );

        $item = $this->parseFieldsImportValue(
            $item,
            $fieldsToParse,
            $this->parseUpdateOnly
        );

        $this->eventsManager->fire(self::class . ':beforeModelSave', $this, $item);
        $this->eventsManager->fire(AdminitemController::class . ':beforeModelSave', new AdminitemController(), $item);
        $item->save();

        $this->logService->write(
            $item->getId(),
            Item::class,
            'Imported "' . $item->getNameField() . '" <a href="' . $this->urlService->getBaseUri() . $item->getSlug() . '" target="_blank">view page</a>'
        );

        if ($parentItem !== null):
            $this->setParentItemImage($item, $parentItem);
        endif;
    }

    protected function getItemFromDatagroupPath(
        DatagroupIterator $categoryGroups,
        ImportType        $importType,
        array             $data,
        array             $header,
        Language          $language
    ): ?Item
    {
        $parentId = null;
        $parentItem = null;

        while ($categoryGroups->valid()) :
            $categoryGroup = $categoryGroups->current();
            $key = $categoryGroups->key();

            if ((string)$categoryGroup->getId() !== $importType->getDatagroup()) :
                $parentTitle = '';
                if (MongoUtil::isObjectId($importType->_('category_' . $key))) :
                    $item = $this->repositories->item->getById($importType->_('category_' . $key));
                    if ($item !== null) :
                        $parentTitle = $item->getNameField();
                    endif;
                else :
                    $parentTitle = $data[$header[$importType->_('category_' . $key)]];
                endif;

                if (!empty($parentTitle)) :
                    if(empty($parentId)):
                        $findParentId = new FindValue('parentId', [null,''], 'in');
                    else :
                        $findParentId = new FindValue('parentId', $parentId);
                    endif;

                    $parentItem = $this->repositories->item->findFirst(
                        new FindValueIterator([
                            new FindValue('datagroup', (string)$categoryGroup->getId()),
                            $findParentId,
                            new FindValue('name.' . $language->getShortCode(), $parentTitle),
                        ]),
                        false
                    );
                endif;

                if ($parentItem === null && !empty($parentTitle)) :
                    $parentItem = ItemFactory::create(
                        $parentTitle,
                        (string)$categoryGroup->getId(),
                        [],
                        true,
                        $parentId
                    );
                    $parentItem->save();
                endif;

                if($parentItem !== null) :
                    $parentId = (string)$parentItem->getId();
                endif;
            endif;
            $categoryGroups->next();
        endwhile;

        return $parentItem;
    }

    protected function getBaseItem(
        array      $uniqueFields,
        Language   $language,
        array      $data,
        array      $header,
        ImportType $importType,
        Item       $parentItem,
                   $headerNameField
    ): Item
    {
        foreach ($uniqueFields as $calling_name => $headerField) :
            Item::setFindValue(
                $calling_name . '.' . $language->getShortCode(),
                $data[$header[$headerField]]
            );
        endforeach;

        Item::setFindPublished(false);
        Item::setFindValue('datagroup', $importType->_('datagroup'));
        Item::setFindValue('parentId', (string)$parentItem->getId());
        $item = Item::findFirst();
        $this->parseUpdateOnly = true;
        if (!$item) :
            $item = ItemFactory::create(
                $data[$header[$headerNameField]],
                $importType->_('datagroup'),
                [],
                true,
                (string)$parentItem->getId()
            );
            $this->parseUpdateOnly = false;
        endif;

        return $item;
    }

    protected function parseFields(
        ImportDatafieldIterator $fieldsToParse,
        bool                    $parseUpdateOnly,
        array                   $data,
        array                   $header,
        Item                    $item,
        Language                $language
    ): Item
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if (
                $parseUpdateOnly === false
                || ($parseUpdateOnly === true && $datafield->isUpdate())
            ) :
                $value = '';
                if (isset($header[$datafield->getHeader()], $data[$header[$datafield->getHeader()]])) :
                    $value = $data[$header[$datafield->getHeader()]];
                endif;

                if (empty($value)) :
                    $value = $datafield->getEmptyValue();
                endif;

                if ($datafield->isMultilang()) :
                    $item->set($datafield->getCallingName(), $value, true, $language->getShortCode());
                else :
                    $item->set($datafield->getCallingName(), $value);
                endif;
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $item;
    }

    protected function parseFieldsImportValue(
        Item                    $item,
        ImportDatafieldIterator $fieldsToParse,
                                $parseUpdateOnly
    ): Item
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if (
                $parseUpdateOnly === false
                || ($parseUpdateOnly === true && $datafield->isUpdate())
            ) :
                if (!$datafield->isMultilang()) :
                    $this->eventsManager->fire($datafield->getType() . ':parse', $item, $datafield);
                endif;
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $item;
    }

    protected function setParentItemImage(Item $item, Item $parentItem): void
    {
        if (
            $parentItem->_('image') === ''
            && $item->_('image') !== ''
        ) :
            $parentItem->set('image', $item->_('image'));
            $parentItem->save();
        endif;
    }
}
