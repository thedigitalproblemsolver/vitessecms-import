<?php declare(strict_types=1);

namespace VitesseCms\Import\Repositories;

use VitesseCms\Database\Models\FindValueIterator;
use VitesseCms\Import\Models\ImportType;
use VitesseCms\Import\Models\ImportTypeIterator;

class ImportTypeRepository
{
    public function getById(string $id, bool $hideUnpublished = true): ?ImportType
    {
        ImportType::setFindPublished($hideUnpublished);

        /** @var ImportType $item */
        $item = ImportType::findById($id);
        if (is_object($item)):
            return $item;
        endif;

        return null;
    }

    public function findAll(
        ?FindValueIterator $findValues = null,
        bool $hideUnpublished = true
    ): ImportTypeIterator
    {
        ImportType::setFindPublished($hideUnpublished);
        ImportType::addFindOrder('name');
        $this->parsefindValues($findValues);

        return new ImportTypeIterator(ImportType::findAll());
    }

    protected function parsefindValues(?FindValueIterator $findValues = null): void
    {
        if ($findValues !== null) :
            while ($findValues->valid()) :
                $findValue = $findValues->current();
                ImportType::setFindValue(
                    $findValue->getKey(),
                    $findValue->getValue(),
                    $findValue->getType()
                );
                $findValues->next();
            endwhile;
        endif;
    }
}
