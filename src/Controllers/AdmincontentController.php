<?php declare(strict_types=1);

namespace VitesseCms\Import\Controllers;

use VitesseCms\Import\Forms\ImportTypeForm;
use VitesseCms\Import\Models\ImportType;

class AdmincontentController extends AbstractImportController
{
    public function onConstruct()
    {
        parent::onConstruct();

        $this->class = ImportType::class;
        $this->classForm = ImportTypeForm::class;
    }
}
