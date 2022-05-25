<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class ChildrenIdsProvider implements \MageSuite\GoogleReviewsFeed\Model\ResourceModel\ChildrenIdsProvider\ChildrenIdsProviderInterface
{
    protected $childrenIdsProvider;

    public function __construct(array $childrenIdsProvider = [])
    {
        $this->childrenIdsProvider = $childrenIdsProvider;
    }

    /**
     * Retrieve first child product ID
     */
    public function getChildrenIds(array $productIds): array
    {
        $childrenIds = [];

        foreach ($this->childrenIdsProvider as $childrenIdsProvider) {
            $childrenIds += $childrenIdsProvider->getChildrenIds($productIds);
        }

        return $childrenIds;
    }
}
