<?php declare(strict_types=1);

namespace VitesseCms\Import\Forms;

use VitesseCms\Admin\Interfaces\AdminModelFormInterface;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Form\AbstractForm;
use VitesseCms\Form\Helpers\ElementHelper;
use VitesseCms\Form\Models\Attributes;
use VitesseCms\Import\Helpers\AbstractImportHelper;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Utils\ImportUtil;
use Phalcon\Tag;

class ImportTypeForm extends AbstractForm implements AdminModelFormInterface
{
    public function buildForm(): void
    {
        $files = ImportUtil::getImporters(
            [
                $this->configuration->getVendorNameDir() . 'import/src/Helpers/',
                $this->configuration->getAccountDir() . 'src/import/Helpers/',
            ]
        );

        $this->addText('%CORE_NAME%', 'name', (new Attributes())->setRequired()->setMultilang())
            ->addDropdown(
                '%ADMIN_DATAGROUP%',
                'datagroup',
                (new Attributes())->setOptions(ElementHelper::arrayToSelectOptions(Datagroup::findAll()))
            )->addDropdown(
                '%IMPORT_TYPE%',
                'type',
                (new Attributes())->setRequired(true)->setOptions(ElementHelper::arrayToSelectOptions($files))
            )
            ->addText('Image folder', 'imageFolder', (new Attributes())->setRequired(true));

        if ($this->entity !== null) :
            if ($this->entity->getType() !== null) :
                /** @var AbstractImportHelper $class */
                $class = $this->entity->getType();
                $class::buildAdminForm($this, $this->entity);
            endif;
            $this->addHtml(Tag::linkTo(
                [
                    'action' => 'import/index/index/' . $this->entity->getId(),
                    'text' => '%IMPORT_TRIGGER_IMPORT%'
                ]
            ));
        endif;

        $this->addSubmitButton('%CORE_SAVE%');
    }
}
