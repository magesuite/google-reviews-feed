<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model;

class ProductDataRepository
{
    protected $productDataCache = [];

    protected $childIds = null;

    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;

    protected \MageSuite\GoogleReviewsFeed\Model\ProductDataFactory $productDataFactory;

    protected \MageSuite\GoogleReviewsFeed\Model\ChildrenIdsProvider $childrenIdsProvider;

    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \MageSuite\GoogleReviewsFeed\Model\ProductDataFactory $productDataFactory,
        \MageSuite\GoogleReviewsFeed\Model\ChildrenIdsProvider $childrenIdsProvider,
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productDataFactory = $productDataFactory;
        $this->childrenIdsProvider = $childrenIdsProvider;
        $this->configuration = $configuration;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return ProductData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData($productId, $storeId): \MageSuite\GoogleReviewsFeed\Model\ProductData
    {
        if (!isset($this->productDataCache[$storeId][$productId])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            );
        }

        return $this->productDataCache[$storeId][$productId];
    }

    public function processReviewCollection(\Magento\Review\Model\ResourceModel\Review\Collection $collection): void
    {
        $productIdsByStoreId = $this->groupProductIdsByStoreId($collection);
        $this->addChildProductIds($productIdsByStoreId);

        foreach ($productIdsByStoreId as $storeId => $productIds) {
            $productCollection = $this->productCollectionFactory->create()
                ->setStoreId($storeId)
                ->addAttributeToFilter('entity_id', ['in' => $productIds])
                ->addAttributeToSelect(['brand', 'name', $this->getGtinAttribute()])
                ->addUrlRewrite();

            foreach ($productCollection as $product) {
                $this->addProductToCache($product);
            }

            $productCollection->clear();
            $this->childIds = null;
        }
    }

    protected function addChildProductIds(array &$productIdsByStoreId): void
    {
        $allIds = array_unique(array_merge(...$productIdsByStoreId));
        $childrenIds = $this->getChildrenIds($allIds);

        foreach ($productIdsByStoreId as $storeId => $productId) {
            foreach ($childrenIds as $childrenId) {
                if (in_array($childrenId, $productIdsByStoreId[$storeId])) {
                    continue;
                }

                $productIdsByStoreId[$storeId][] = $childrenId;
            }
        }
    }

    protected function addProductToCache(\Magento\Catalog\Model\Product $product): void
    {
        $productData = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'gtin' => $product->getData($this->getGtinAttribute()),
            'brand' => $product->getAttributeText('brand'),
            'url' => $product->getProductUrl(),
            'isComposite' => $product->isComposite(),
            'childrenIds' => $this->childIds[$product->getId()] ?? []
        ];
        $this->productDataCache[$product->getStoreId()][$product->getId()] = $this->productDataFactory->create($productData);
    }

    protected function groupProductIdsByStoreId(\Magento\Review\Model\ResourceModel\Review\Collection $collection): array
    {
        $productIdsByStore = [];

        foreach ($collection as $review) {
            $productId = $review->getEntityPkValue();

            if (!isset($productIdsByStore[$review->getStoreId()])) {
                $productIdsByStore[$review->getStoreId()] = [];
            }

            if (in_array($productId, $productIdsByStore[$review->getStoreId()])
                || isset($this->productDataCache[$review->getStoreId()][$productId])) {
                continue;
            }

            $productIdsByStore[$review->getStoreId()][] = $productId;
        }

        return $productIdsByStore;
    }

    protected function getChildrenIds(array $productIds): array
    {
        if ($this->childIds === null) {
            $this->childIds = $this->childrenIdsProvider->getChildrenIds($productIds);
        }

        return $this->childIds;
    }

    protected function getGtinAttribute(): string
    {
        return $this->configuration->getGtinAttribute();
    }
}
