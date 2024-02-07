<?php

declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Import\Enum\ImportEnum;
use VitesseCms\Import\Enum\ImportTypeEnum;
use VitesseCms\Import\Listeners\Admin\AdminMenuListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldImageListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldPriceListener;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Import\Repositories\RepositoryCollection;
use VitesseCms\Language\Models\Language;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Media\Fields\Image;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $injectable): void
    {
        if ($injectable->user->hasAdminAccess()):
            $injectable->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $injectable->eventsManager->attach(
            Image::class,
            new ImportFieldImageListener(
                $injectable->configuration,
                $injectable->url
            )
        );
        $injectable->eventsManager->attach('FieldPrice', new ImportFieldPriceListener());
        $injectable->eventsManager->attach(
            ImportTypeEnum::IMPORTTYPE_LISTENER->value,
            new ImportTypeListener(new ImportTypeRepository())
        );
        $injectable->eventsManager->attach(
            ImportEnum::IMPORT_HANDLER_LISTENER,
            new ImportLineHandlerListener(
                new RepositoryCollection(
                    new ImportTypeRepository(),
                    new LanguageRepository(Language::class),
                    new DatagroupRepository(),
                    new ItemRepository(),
                    new ImportDatafieldRepository(),
                    new DatafieldRepository()
                ),
                $injectable->eventsManager,
                $injectable->log,
                $injectable->url
            )
        );
    }
}
