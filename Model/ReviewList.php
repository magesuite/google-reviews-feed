<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class ReviewList
{
    protected \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collecitonFactory;

    protected \Magento\Review\Model\ReviewFactory $reviewFactory;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->collecitonFactory = $collectionFactory;
        $this->reviewFactory = $reviewFactory;
    }

    public function getItems(): \Magento\Review\Model\ResourceModel\Review\Collection
    {
        $collection = $this->collecitonFactory->create()
            ->setPageSize(1000)
            ->addFieldToFilter('entity_id', $this->getEntityId())
            ->addFieldToFilter('entity_pk_value', ['gt' => 0])
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();
        $collection->getSelect()->columns('detail.store_id');

        return $collection;
    }

    protected function getEntityId()
    {
        return $this->reviewFactory->create()->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE);
    }
}
