<?php declare(strict_types=1);

namespace VitesseCms\Import\Controllers;

use VitesseCms\Core\AbstractController;
use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Import\Enum\ImportEnum;
use VitesseCms\Import\Helpers\AbstractImportHelper;
use VitesseCms\Import\Helpers\ImportLineEventVehicle;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Repositories\RepositoriesInterface;

class IndexController extends AbstractController implements RepositoriesInterface
{
    public function IndexAction(): void
    {
        set_time_limit(300);

        if ($this->dispatcher->getParam(0)):
            $importType = $this->repositories->importType->getById($this->dispatcher->getParam(0));
            if ($importType === null) {
                echo 'no importtype find';
                die();
            }

            /** @var AbstractImportHelper $importHelper */
            $importHelper = $importType->getImportHelper();
            if ($importHelper !== null) :
                $language = $this->repositories->language->getById($importType->getLanguage());
                $datagroup = $this->repositories->datagroup->getById($importType->getDatagroup());
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
                            $importType->getNameField().' : '.ImportEnum::IMPORT_HANDLER_PARSELINE_EVENT,
                            ImportEnum::IMPORT_HANDLER_PARSELINE_EVENT,
                            ImportLineEventVehicle::create(
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
                endif;
            endif;
        endif;

        $this->redirect();
    }

    protected function getFieldsToParse(ImportType $importType, Datagroup $datagroup): ImportDatafieldIterator
    {
        $fieldsToParse = new ImportDatafieldIterator();
        foreach ((array)$datagroup->_('datafields') as $datafieldValue) :
            $datafield = $this->repositories->importDatafield->getById($datafieldValue['id']);
            if ($datafield !== null) :
                if (
                    $importType->_('importField_' . $datafield->getCallingName())
                    || $importType->_('importField_empty_' . $datafield->getCallingName())
                ) :
                    $datafield->setHeader(
                        $importType->_('importField_' . $datafield->getCallingName())
                    );
                    $datafield->setEmptyValue(
                        $importType->_('importField_empty_' . $datafield->getCallingName())
                    );
                    $datafield->setUpdate(
                        (bool)$importType->_('importField_update_' . $datafield->getCallingName())
                    );
                    $datafield->setUnique(
                        (bool)$importType->_('importField_unique_' . $datafield->getCallingName())
                    );
                    $datafield->setImageFolder($importType->getImageFolder());

                    $fieldsToParse->add($datafield);
                endif;
            endif;
        endforeach;

        return $fieldsToParse;
    }

    protected function parseUrl(string $url): array
    {
        $handle = fopen($url, 'rb');
        $httpHeaders = get_headers($url, 1);
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
