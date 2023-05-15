<?php declare(strict_types=1);

namespace VitesseCms\Import\Listeners;

use VitesseCms\Import\Repositories\ImportTypeRepository;

class ImportTypeListener
{
    public function __construct(private readonly ImportTypeRepository $importTypeRepository){}

    public function getRepository(): ImportTypeRepository
    {
        return $this->importTypeRepository;
    }
}
