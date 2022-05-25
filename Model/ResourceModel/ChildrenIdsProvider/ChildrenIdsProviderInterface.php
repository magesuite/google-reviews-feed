<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ResourceModel\ChildrenIdsProvider;

interface ChildrenIdsProviderInterface
{
    public function getChildrenIds(array $productIds): array;
}
