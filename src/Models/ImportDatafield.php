<?php declare(strict_types=1);

namespace VitesseCms\Import\Models;

use VitesseCms\Datafield\Models\Datafield;

class ImportDatafield extends Datafield
{
    /**
     * @var string
     */
    public $imageFolder;
    /**
     * @var string
     */
    protected $header;
    /**
     * @var string
     */
    protected $emptyValue;
    /**
     * @var bool
     */
    protected $update;
    /**
     * @var bool
     */
    protected $unique;

    public function getSource()
    {
        return 'datafield';
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header): ImportDatafield
    {
        $this->header = $header;

        return $this;
    }

    public function getEmptyValue(): string
    {
        return $this->emptyValue;
    }

    public function setEmptyValue(string $emptyValue): ImportDatafield
    {
        $this->emptyValue = $emptyValue;

        return $this;
    }

    public function isUpdate(): bool
    {
        return $this->update;
    }

    public function setUpdate(bool $update): ImportDatafield
    {
        $this->update = $update;

        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): ImportDatafield
    {
        $this->unique = $unique;

        return $this;
    }

    public function getImageFolder(): ?string
    {
        return $this->imageFolder;
    }

    public function setImageFolder(string $imageFolder): ImportDatafield
    {
        $this->imageFolder = $imageFolder;

        return $this;
    }
}
