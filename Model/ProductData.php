<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class ProductData
{
    protected int $id;

    protected string $name;

    protected string $sku;

    protected string $ean;

    protected string $brand;

    protected string $url;

    protected bool $isComposite;

    protected array $childrenIds;

    public function __construct($id, $name, $sku, $ean, $brand, $url, $isComposite, $childrenIds)
    {
        $this->id = (int)$id;
        $this->name = (string)$name;
        $this->sku = (string)$sku;
        $this->ean = (string)$ean;
        $this->brand = (string)$brand;
        $this->url = (string)$url;
        $this->isComposite = (bool)$isComposite;
        $this->childrenIds = (array)$childrenIds;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isComposite(): bool
    {
        return $this->isComposite;
    }

    public function getChildrenIds(): array
    {
        return $this->childrenIds;
    }
}
