<?php declare(strict_types=1);

namespace VitesseCms\Import\Migrations;

use VitesseCms\Cli\Services\TerminalServiceInterface;
use VitesseCms\Configuration\Services\ConfigServiceInterface;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Import\Repositories\RepositoryCollection;
use VitesseCms\Install\Interfaces\MigrationInterface;
use VitesseCms\Language\Repositories\LanguageRepository;

class Migration_20210429 implements MigrationInterface
{
    /**
     * @var RepositoryCollection
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new RepositoryCollection(
            new ImportTypeRepository(),
            new LanguageRepository(),
            new DatagroupRepository(),
            new ItemRepository(),
            new ImportDatafieldRepository(),
            new DatafieldRepository()
        );
    }

    public function up(
        ConfigServiceInterface $configService,
        TerminalServiceInterface $terminalService
    ): bool
    {
        $result = true;
        if (!$this->parseImports($terminalService)) :
            $result = false;
        endif;

        return $result;
    }

    private function parseImports(TerminalServiceInterface $terminalService): bool
    {
        $result = true;
        $importTypes = $this->repository->importType->findAll(null, false);

        $search = ['Modules\\'];
        $replace = ['VitesseCms\\'];
        while ($importTypes->valid()):
            $importType = $importTypes->current();
            $type = str_replace($search, $replace, $importType->getType());
            if (substr($type, 0, 11) === 'VitesseCms\\') :
                $importType->setType($type)->save();
            else :
                $terminalService->printError('wrong type "' . $type . '" for importType "' . $importType->getNameField() . '"');
                $result = false;
            endif;

            $importTypes->next();
        endwhile;

        $terminalService->printMessage('ImportType type repaired');

        return $result;
    }
}