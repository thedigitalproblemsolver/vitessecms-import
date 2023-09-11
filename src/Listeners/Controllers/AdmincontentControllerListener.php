<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners\Controllers;

use Phalcon\Events\Event;
use VitesseCms\Admin\Forms\AdminlistFormInterface;
use VitesseCms\Import\Controllers\AdmincontentController;

class AdmincontentControllerListener
{
    public function adminListFilter(Event $event, AdmincontentController $controller, AdminlistFormInterface $form): void
    {
        $form->addText('%CORE_NAME%', 'filter[name]');
        $form->addPublishedField($form);
    }
}