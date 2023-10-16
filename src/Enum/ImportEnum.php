<?php declare(strict_types=1);

namespace VitesseCms\Import\Enum;

use VitesseCms\Core\AbstractEnum;

class ImportEnum extends AbstractEnum {
    public const IMPORT_HANDLER_LISTENER = 'importHandler';
    public const IMPORT_HANDLER_PARSELINE_EVENT = 'importHandler:parseLine';
}