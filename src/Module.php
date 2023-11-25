<?php
declare(strict_types=1);

namespace VitesseCms\Import;

use Phalcon\Di\DiInterface;
use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\AbstractModule;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Import\Repositories\RepositoryCollection;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Repositories\LanguageRepository;

class Module extends AbstractModule
{
    public function registerServices(DiInterface $di, string $string = null)
    {
        parent::registerServices($di, 'Import');

        $di->setShared(
            'repositories',
            new RepositoryCollection(
                new ImportTypeRepository(),
                new LanguageRepository(Language::class),
                new DatagroupRepository(),
                new ItemRepository(),
                new ImportDatafieldRepository(),
                new DatafieldRepository()
            )
        );
    }
}
