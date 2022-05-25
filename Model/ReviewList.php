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
            ->addFieldToFilter('detail.detail', ['nlike' => '%http%'])
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();
        $collection->getSelect()->columns('detail.store_id');
        $this->addRatingPercent($collection);

        return $collection;
    }

    protected function addRatingPercent(\Magento\Review\Model\ResourceModel\Review\Collection $collection): void
    {
        $collection->getSelect()->join(
            ['rov' => $collection->getTable('rating_option_vote')],
            'main_table.review_id = rov.review_id',
            ['rating_percent' => 'rov.percent']
        )->group('main_table.review_id');
    }

    protected function getEntityId(): int
    {
        return (int)$this->reviewFactory->create()->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
    }
}
