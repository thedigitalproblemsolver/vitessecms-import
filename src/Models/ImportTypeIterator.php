<?php declare(strict_types=1);

namespace VitesseCms\Import\Models;

use ArrayIterator;

class ImportTypeIterator extends ArrayIterator
{
    public function __construct(array $blocks)
    {
        parent::__construct($blocks);
    }

    public function current(): ImportType
    {
        return parent::current();
    }
}
