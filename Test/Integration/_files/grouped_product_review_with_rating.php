<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Customer/_files/customer.php');
$resolver->requireDataFixture('Magento/GroupedProduct/_files/product_grouped_with_simple.php');
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$customerRegistry = $objectManager->create(\Magento\Customer\Model\CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('grouped');
$storeId = $objectManager->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();

$review = $objectManager->create(
    \Magento\Review\Model\Review::class,
    ['data' => [
        'customer_id' => $customer->getId(),
        'title' => 'Review Summary',
        'detail' => 'Review text',
        'nickname' => 'Nickname',
    ]]
);

$review
    ->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
    ->setEntityPkValue($product->getId())
    ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
    ->setStoreId($storeId)
    ->setStores([$storeId])
    ->save();

$objectManager->get(\Magento\Framework\Registry::class)->register(
    'review_data_grouped',
    $review
);

/** @var Collection $ratingCollection */
$ratingCollection = $objectManager->create(
    \Magento\Review\Model\Rating::class
)->getCollection()
    ->setPageSize(2)
    ->setCurPage(1);

foreach ($ratingCollection as $rating) {
    $rating->setStores([$storeId])->setIsActive(1)->save();
}

foreach ($ratingCollection as $rating) {
    $ratingOption = $objectManager
        ->create(\Magento\Review\Model\Rating\Option::class)
        ->getCollection()
        ->setPageSize(1)
        ->setCurPage(2)
        ->addRatingFilter($rating->getId())
        ->getFirstItem();
    $rating->setReviewId($review->getId())
        ->addOptionVote($ratingOption->getId(), $product->getId());
}

$objectManager->get(\Magento\Framework\Registry::class)->register(
    'rating_data_grouped',
    $ratingCollection->getFirstItem()
);
$review->aggregate();
