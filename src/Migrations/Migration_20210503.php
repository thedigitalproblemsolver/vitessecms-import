<?php

declare(strict_types=1);

namespace VitesseCms\Import\Migrations;

use stdClass;
use VitesseCms\Database\AbstractMigration;
use VitesseCms\Import\Enum\ImportTypeEnum;

class Migration_20210503 extends AbstractMigration
{
    public function up(): bool
    {
        $result = true;
        if (!$this->parseImports()) :
            $result = false;
        endif;

        return $result;
    }

    private function parseImports(): bool
    {
        $result = true;
        $importTypeRepository = $this->eventsManager->fire(ImportTypeEnum::GET_REPOSITORY->value, new stdClass());
        $importTypes = $importTypeRepository->findAll(null, false);

        $search = ['Modules\\'];
        $replace = ['VitesseCms\\'];
        while ($importTypes->valid()):
            $importType = $importTypes->current();
            $type = str_replace($search, $replace, $importType->getType());
            if (str_starts_with($type, 'VitesseCms\\')) :
                $importType->setType($type)->save();
            else :
                $this->terminalService->printError(
                    'wrong type "' . $type . '" for importType "' . $importType->getNameField() . '"'
                );
                $result = false;
            endif;

            $importTypes->next();
        endwhile;

        $this->terminalService->printMessage('ImportType type repaired');

        return $result;
    }
}