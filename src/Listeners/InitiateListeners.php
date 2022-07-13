<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Import\Enum\ImportEnum;
use VitesseCms\Import\Listeners\Admin\AdminMenuListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldImageListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldPriceListener;
use VitesseCms\Import\Repositories\ImportDatafieldRepository;
use VitesseCms\Import\Repositories\ImportTypeRepository;
use VitesseCms\Import\Repositories\RepositoryCollection;
use VitesseCms\Language\Repositories\LanguageRepository;
use VitesseCms\Media\Fields\Image;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        if($di->user->hasAdminAccess()):
            $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        endif;
        $di->eventsManager->attach(Image::class, new ImportFieldImageListener(
            $di->configuration,
            $di->url
        ));
        $di->eventsManager->attach('FieldPrice', new ImportFieldPriceListener());
        $di->eventsManager->attach(ImportEnum::IMPORT_HANDLER_LISTENER, new ImportLineHandlerListener(
            new RepositoryCollection(
                new ImportTypeRepository(),
                new LanguageRepository(),
                new DatagroupRepository(),
                new ItemRepository(),
                new ImportDatafieldRepository(),
                new DatafieldRepository()
            ),
            $di->eventsManager,
            $di->log,
            $di->url
        ));
    }
}
