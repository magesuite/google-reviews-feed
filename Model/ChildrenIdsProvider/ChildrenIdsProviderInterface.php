<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ChildrenIdsProvider;

interface ChildrenIdsProviderInterface
{
    public function getChildrenIds(array $productIds): array;
}
