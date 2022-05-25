<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ResourceModel\ChildrenIdsProvider;

class Configurable implements ChildrenIdsProviderInterface
{
    protected \Magento\Framework\App\ResourceConnection $resourceConnection;

    protected \MageSuite\GoogleReviewsFeed\Model\ResourceModel\GtinProcessor $gtinProcessor;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MageSuite\GoogleReviewsFeed\Model\ResourceModel\GtinProcessor $gtinProcessor
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->gtinProcessor = $gtinProcessor;
    }

    public function getChildrenIds(array $productIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $connection->getTableName('catalog_product_super_link')],
                ['parent_id', 'product_id']
            )
            ->where('main_table.parent_id IN (?)', $productIds)
            ->group('main_table.parent_id'); // need only first child
        $this->gtinProcessor->execute($select);

        return (array)$connection->fetchPairs($select);
    }
}
