<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

class RepositoryCollection implements RepositoryInterface
{
    /**
     * @var ImportTypeRepository
     */
    public $importType;

    /**
     * @var LanguageRepository
     */
    public $language;

    /**
     * @var DatagroupRepository
     */
    public $datagroup;

    /**
     * @var ItemRepository
     */
    public $item;

    /**
     * @var ImportDatafieldRepository
     */
    public $importDatafield;

    /**
     * @var DatafieldRepository
     */
    public $datafield;

    public function __construct(
        ImportTypeRepository $importTypeRepository,
        LanguageRepository $languageRepository,
        DatagroupRepository $datagroupRepository,
        ItemRepository $itemRepository,
        ImportDatafieldRepository $importDatafieldRepository,
        DatafieldRepository $datafieldRepository
    )
    {
        $this->importType = $importTypeRepository;
        $this->language = $languageRepository;
        $this->datagroup = $datagroupRepository;
        $this->item = $itemRepository;
        $this->importDatafield = $importDatafieldRepository;
        $this->datafield = $datafieldRepository;
    }
}
