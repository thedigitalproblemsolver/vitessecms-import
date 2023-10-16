<?php
declare(strict_types=1);

namespace VitesseCms\Import\Controllers;

use stdClass;
use VitesseCms\Core\AbstractController;
use VitesseCms\Core\AbstractControllerFrontend;
use VitesseCms\Datafield\Enum\DatafieldEnum;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Enums\DatagroupEnum;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Import\Enum\ImportEnum;
use VitesseCms\Import\Enum\ImportTypeEnum;
use VitesseCms\Import\Factories\ImportDatafieldFactory;
use VitesseCms\Import\Helpers\AbstractImportHelper;
use VitesseCms\Import\Helpers\ImportLineEventVehicle;
use VitesseCms\Import\Models\ImportDatafield;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Import\Repositories\RepositoriesInterface;
use VitesseCms\Language\Enums\LanguageEnum;
use VitesseCms\Language\Repositories\LanguageRepository;

class IndexController extends AbstractControllerFrontend
{
    private ImportTypeRepository $importTypeRepository;
    private LanguageRepository $languageRepository;
    private DatagroupRepository $datagroupRepository;
    private DatafieldRepository $datafieldRepository;

    public function OnConstruct()
    {
        parent::onConstruct();
        $this->importTypeRepository = $this->eventsManager->fire(ImportTypeEnum::GET_REPOSITORY->value, new stdClass());
        $this->languageRepository = $this->eventsManager->fire(LanguageEnum::GET_REPOSITORY->value, new stdClass());
        $this->datagroupRepository = $this->eventsManager->fire(DatagroupEnum::GET_REPOSITORY->value, new stdClass());
        $this->datafieldRepository = $this->eventsManager->fire(DatafieldEnum::GET_REPOSITORY->value, new stdClass());
    }


    public function IndexAction(string $id): void
    {
        set_time_limit(300);

        $importType = $this->importTypeRepository->getById($id);
        if ($importType === null) {
            echo 'no importtype find';
            die();
        }

        $importHelper = $importType->getImportHelper();
        if ($importHelper !== null) :
            $language = $this->languageRepository->getById($importType->getLanguage());
            $datagroup = $this->datagroupRepository->getById($importType->getDatagroup());
            if ($importHelper->isNew()) :
                $importHelper->processImport($importType);
            elseif (
                $language !== null
                && $datagroup !== null
                && ($handle = fopen($importType->_('url'), 'rb')) !== false
            ) :
                $fieldsToParse = $this->getFieldsToParse($importType, $datagroup);

                $parsedUrl = $this->parseUrl($importType->_('url'));
                $header = $parsedUrl['header'];
                $importData = (array)$parsedUrl['importData'];
                $uniqueFields = $this->getUniqueFields($fieldsToParse);
                $headerNameField = $this->getNameField($fieldsToParse);

                foreach ($importData as $data) :
                    $this->jobQueue->createListenerJob(
                        $importType->getNameField() . ' : ' . ImportEnum::IMPORT_HANDLER_PARSELINE_EVENT,
                        ImportEnum::IMPORT_HANDLER_PARSELINE_EVENT,
                        new ImportLineEventVehicle(
                            $datagroup,
                            $importType,
                            $data,
                            $header,
                            $language,
                            $uniqueFields,
                            $headerNameField,
                            $fieldsToParse
                        )
                    );
                endforeach;
                $this->flashService->setSucces('IMPORT_IMPORT_SUCCEEDED');
            endif;
        endif;

        $this->redirect($this->request->getHTTPReferer());
    }

    protected function getFieldsToParse(ImportType $importType, Datagroup $datagroup): ImportDatafieldIterator
    {
        $fieldsToParse = new ImportDatafieldIterator();
        foreach ((array)$datagroup->_('datafields') as $datafieldValue) :
            $datafield = $this->datafieldRepository->getById($datafieldValue['id']);
            if ($datafield !== null) :
                if (
                    $importType->_('importField_' . $datafield->getCallingName())
                    || $importType->_('importField_empty_' . $datafield->getCallingName())
                ) :
                    $importDatafield = ImportDatafieldFactory::createFromDatefield($datafield);
                    $importDatafield->setHeader(
                        $importType->_('importField_' . $datafield->getCallingName())
                    );
                    $importDatafield->setEmptyValue(
                        $importType->_('importField_empty_' . $datafield->getCallingName())
                    );
                    $importDatafield->setUpdate(
                        (bool)$importType->_('importField_update_' . $datafield->getCallingName())
                    );
                    $importDatafield->setUnique(
                        (bool)$importType->_('importField_unique_' . $datafield->getCallingName())
                    );
                    $importDatafield->setImageFolder($importType->getImageFolder());

                    $fieldsToParse->add($importDatafield);
                endif;
            endif;
        endforeach;

        return $fieldsToParse;
    }

    protected function parseUrl(string $url): array
    {
        $handle = fopen($url, 'rb');
        $httpHeaders = get_headers($url, true);
        $header = $importData = [];
        switch (strtolower($httpHeaders['Content-Type'][1])) :
            case 'text/csv':
            case 'text/csv; charset=utf-8':
                while (($header = fgetcsv($handle, 3000, ',')) !== false) :
                    break;
                endwhile;

                $row = 0;
                while (($data = fgetcsv($handle, 3000, ',')) !== false) :
                    if ($row > 0) :
                        $importData[] = $data;
                    endif;
                    $row++;
                endwhile;
                fclose($handle);
                $header = array_flip($header);
                break;
            case 'application/json':
            case 'application/json; charset=utf-8':
                $importData = json_decode(file_get_contents($url));
                foreach ((array)$importData[0] as $name => $value) :
                    $header[] = $name;
                endforeach;
                $header = array_combine($header, $header);
                break;
        endswitch;

        return [
            'header' => $header,
            'importData' => $importData,
        ];
    }

    protected function getUniqueFields(ImportDatafieldIterator $fieldsToParse): array
    {
        $fields = [];

        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if ($datafield->isUnique()) :
                $fields[$datafield->getCallingName()] = $datafield->getHeader();
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return $fields;
    }

    protected function getNameField(ImportDatafieldIterator $fieldsToParse): string
    {
        while ($fieldsToParse->valid()) :
            $datafield = $fieldsToParse->current();
            if ($datafield->getCallingName() === 'name') :
                $fieldsToParse->rewind();

                return $datafield->getHeader();
            endif;
            $fieldsToParse->next();
        endwhile;
        $fieldsToParse->rewind();

        return 'name';
    }
}
