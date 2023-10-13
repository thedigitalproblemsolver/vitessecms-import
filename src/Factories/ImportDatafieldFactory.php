<?php declare(strict_types=1);

namespace VitesseCms\Import\Factories;

use VitesseCms\Datafield\Models\Datafield;
use VitesseCms\Import\Models\ImportDatafield;

class ImportDatafieldFactory {
    public static function createFromDatefield(Datafield $datafield): ImportDatafield
    {
        $importDatafield = new ImportDatafield();
        foreach($datafield as $k => $v) {
            $importDatafield->$k = $v;
        }

        return $importDatafield;
    }
}