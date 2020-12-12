<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Content\Models\Item;
use VitesseCms\Import\Models\ImportDatafield;
use Phalcon\Events\Event;

class ImportFieldDatagroupListener
{
    public function parse(Event $event, Item $item, ImportDatafield $importDatafield): void
    {
        echo 'Wordt deze gebruit? Zo ja, nog te verwerken';
    }
}
