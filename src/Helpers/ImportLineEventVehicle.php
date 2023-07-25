<?php declare(strict_types=1);

namespace VitesseCms\Import\Helpers;

use VitesseCms\Datagroup\Models\Datagroup;
use VitesseCms\Import\Models\ImportDatafieldIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Language\Models\Language;

class ImportLineEventVehicle
{
    public function __construct(
        public readonly Datagroup $datagroup,
        public readonly ImportType $importType,
        public readonly array $data,
        public readonly array $header,
        public readonly Language $language,
        public readonly array $uniqueFields,
        public readonly string $headerNameField,
        public readonly ImportDatafieldIterator $fieldsToParse,
    )
    {
    }
}