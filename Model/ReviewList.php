<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class ReviewList
{
    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collecitonFactory;

    protected \Magento\Review\Model\ReviewFactory $reviewFactory;

    protected int $batchSize;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        $batchSize
    ) {
        $this->collecitonFactory = $collectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->batchSize = (int)$batchSize;
    }

    public function getCollection(): \Magento\Review\Model\ResourceModel\Review\Collection
    {
        $collection = $this->collecitonFactory->create()
            ->setPageSize($this->batchSize)
            ->addFieldToFilter('main_table.entity_id', $this->getEntityId())
            ->addFieldToFilter('main_table.entity_pk_value', ['gt' => 0])
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();
        $collection->getSelect()->columns('detail.store_id');
        $this->addRatingSummaryInfo($collection);

        return $collection;
    }

    protected function addRatingSummaryInfo(\Magento\Review\Model\ResourceModel\Review\Collection $collection): void
    {
        $conditions = implode(' AND ', [
            'main_table.entity_pk_value = summary.entity_pk_value',
            'detail.store_id = summary.store_id'
        ]);
        $collection->getSelect()->join(
            ['summary' => $collection->getTable('review_entity_summary')],
            $conditions,
            ['summary.rating_summary']
        );
    }

    protected function getEntityId(): int
    {
        return (int)$this->reviewFactory->create()->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
    }
}
