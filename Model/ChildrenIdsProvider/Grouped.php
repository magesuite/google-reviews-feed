<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ChildrenIdsProvider;

class Grouped implements ChildrenIdsProviderInterface
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
                [$connection->getTableName('catalog_product_link')],
                ['product_id', 'linked_product_id']
            )
            ->where('link_type_id = ?', \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED)
            ->where('product_id IN (?)', $productIds)
            ->group('product_id'); // need only first child

        return (array)$connection->fetchPairs($select);
    }
}
