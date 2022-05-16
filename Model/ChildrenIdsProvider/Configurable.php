<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ChildrenIdsProvider;

class Configurable implements ChildrenIdsProviderInterface
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getChildrenIds(array $productIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                [$connection->getTableName('catalog_product_super_link')],
                ['parent_id', 'product_id']
            )
            ->where('parent_id IN (?)', $productIds)
            ->group('parent_id'); // need only first child

        return (array)$connection->fetchPairs($select);
    }
}
