<?php declare(strict_types=1);

namespace VitesseCms\Import\Helpers;

use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Language\Models\Language;

class ImportLineEventVehicle {

    /**
     * @var Datagroup
     */
    protected $datagroup;

    /**
     * @var ImportType
     */
    protected $importType;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $header;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var array
     */
    protected $uniqueFields;

    /**
     * @var string
     */
    protected $headerNameField;

    /**
     * @var ImportDatafieldIterator
     */
    protected $fieldsToParse;

    public function setDatagroup(Datagroup $datagroup): ImportLineEventVehicle
    {
        $this->datagroup = $datagroup;

        return $this;
    }

    public function setImportType(ImportType $importType): ImportLineEventVehicle
    {
        $this->importType = $importType;

        return $this;
    }

    public function setData(array $data): ImportLineEventVehicle
    {
        $this->data = $data;

        return $this;
    }

    public function setHeader(array $header): ImportLineEventVehicle
    {
        $this->header = $header;

        return $this;
    }

    public function setLanguage(Language $language): ImportLineEventVehicle
    {
        $this->language = $language;

        return $this;
    }

    public function setUniqueFields(array $uniqueFields): ImportLineEventVehicle
    {
        $this->uniqueFields = $uniqueFields;

        return $this;
    }

    public function setHeaderNameField(string $headerNameField): ImportLineEventVehicle
    {
        $this->headerNameField = $headerNameField;

        return $this;
    }

    public function setFieldsToParse(ImportDatafieldIterator $fieldsToParse): ImportLineEventVehicle
    {
        $this->fieldsToParse = $fieldsToParse;

        return $this;
    }

    public function getDatagroup(): Datagroup
    {
        return $this->datagroup;
    }

    public function getImportType(): ImportType
    {
        return $this->importType;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getUniqueFields(): array
    {
        return $this->uniqueFields;
    }

    public function getHeaderNameField(): string
    {
        return $this->headerNameField;
    }

    public function getFieldsToParse(): ImportDatafieldIterator
    {
        return $this->fieldsToParse;
    }

    public static function create(
        Datagroup $datagroup,
        ImportType $importType,
        array $data,
        array $header,
        Language $language,
        array $uniqueFields,
        string $headerNameField,
        ImportDatafieldIterator $fieldsToParse
    ): self {
        return (new self())
            ->setDatagroup($datagroup)
            ->setImportType($importType)
            ->setData($data)
            ->setHeader($header)
            ->setLanguage($language)
            ->setUniqueFields($uniqueFields)
            ->setHeaderNameField($headerNameField)
            ->setFieldsToParse($fieldsToParse)
        ;
    }
}