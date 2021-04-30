<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Content\Repositories\ItemRepository;
use VitesseCms\Datafield\Repositories\DatafieldRepository;
use VitesseCms\Datagroup\Repositories\DatagroupRepository;
use VitesseCms\Language\Repositories\LanguageRepository;

/**
 * @property ImportTypeRepository $importType
 * @property LanguageRepository $language
 * @property DatagroupRepository $datagroup
 * @property ItemRepository $item
 * @property ImportDatafieldRepository $importDatafield
 * @property DatafieldRepository $datafield
 */
interface RepositoryInterface
{
}
