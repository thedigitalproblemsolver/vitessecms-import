<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use Phalcon\Events\Manager;
use VitesseCms\Import\Controllers\AdmincontentController;

class InitiateAdminListeners
{
    public static function setListeners(Manager $eventsManager): void
    {
        $eventsManager->attach('adminMenu', new AdminMenuListener());
        $eventsManager->attach(AdmincontentController::class, new AdmincontentControllerListener());
    }
}
