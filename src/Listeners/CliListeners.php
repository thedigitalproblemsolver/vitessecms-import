<?php

declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Cli\ConsoleApplication;
use VitesseCms\Cli\Interfaces\CliListenersInterface;
use VitesseCms\Import\Enum\ImportTypeEnum;
use VitesseCms\Import\Repositories\ImportTypeRepository;

class CliListeners implements CliListenersInterface
{
    public static function setListeners(ConsoleApplication $di): void
    {
        $di->eventsManager->attach(
            ImportTypeEnum::IMPORTTYPE_LISTENER->value,
            new ImportTypeListener(new ImportTypeRepository())
        );
    }
}
