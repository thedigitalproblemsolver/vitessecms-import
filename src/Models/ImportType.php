<?php

declare(strict_types=1);

namespace VitesseCms\Import\Models;

use VitesseCms\Database\AbstractCollection;
use VitesseCms\Import\Helpers\AbstractImportHelperInterface;

class ImportType extends AbstractCollection
{
    public string $type;
    public ?string $language;
    public string $datagroup;
    public ?string $imageFolder;
    protected ?AbstractImportHelperInterface $importHelper;

    public function getImportHelper(): ?AbstractImportHelperInterface
    {
        if ($this->type === null):
            return null;
        endif;

        return new $this->type();
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getDatagroup(): ?string
    {
        return $this->datagroup;
    }

    public function getImageFolder(): string
    {
        return $this->imageFolder ?? '';
    }

    public function hasType(): bool
    {
        return $this->type !== null;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): ImportType
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeClass(string $account): string
    {
        if (
            substr_count($this->type, 'VitesseCms\\Import\\Helpers')
            || substr_count($this->type, 'VitesseCms\\' . ucfirst($account) . '\\Import\\Helpers')
        ) :
            return $this->type;
        endif;

        if (substr_count($this->type, 'Modules')) :
            return str_replace('Modules', 'VitesseCms', $this->type);
        endif;

        return 'VitesseCms\\Field\\Models\\' . $this->type;
    }
}
