<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Core\Interfaces\InitiateListenersInterface;
use VitesseCms\Core\Interfaces\InjectableInterface;
use VitesseCms\Import\Controllers\AdmincontentController;
use VitesseCms\Import\Enum\ImportTypeEnum;
use VitesseCms\Import\Listeners\Admin\AdminMenuListener;
use VitesseCms\Import\Listeners\Controllers\AdmincontentControllerListener;
use VitesseCms\Import\Repositories\ImportTypeRepository;

class InitiateAdminListeners implements InitiateListenersInterface
{
    public static function setListeners(InjectableInterface $di): void
    {
        $di->eventsManager->attach('adminMenu', new AdminMenuListener());
        $di->eventsManager->attach(AdmincontentController::class, new AdmincontentControllerListener());
        $di->eventsManager->attach(ImportTypeEnum::IMPORTTYPE_LISTENER->value, new ImportTypeListener(new ImportTypeRepository()));
    }
}
