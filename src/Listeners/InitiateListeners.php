<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Import\Listeners\Admin\AdminMenuListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldImageListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldPriceListener;
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
    }
}
