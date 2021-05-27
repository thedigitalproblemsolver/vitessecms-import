<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Import\Listeners\Admin\AdminMenuListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldImageListener;
use VitesseCms\Import\Listeners\Fields\ImportFieldPriceListener;

class InitiateListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach('FieldImage', new ImportFieldImageListener());
        $di->eventsManager->attach('FieldPrice', new ImportFieldPriceListener());
        $di->eventsManager->attach('adminMenu', new AdminMenuListener());
    }
}
