<?php declare(strict_types=1);

namespace VitesseCms\Import\Helpers;

use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;
use Phalcon\Http\Request\FileInterface;

interface AbstractImportHelperInterface
{
    public static function buildAdminForm(ImportTypeForm $form, ImportType $item): void;

    public function setFile(FileInterface $file): void;

    public function setLanguage(string $languageLocale): void;

    public function setClass(string $class): void;

    public function setFields(array $fields): void;

    public function processImport(?ImportType $importType = null): void;

    public function isNew(): bool;
}
