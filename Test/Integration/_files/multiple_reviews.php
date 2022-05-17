<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Catalog/_files/multiple_products.php');
$objectManager =  \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$storeId =  $objectManager->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();
$review = $objectManager->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => 'Review Summary', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    10
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
   $storeId
)->setStores(
    [$storeId]
)->save();

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
        ->addOptionVote($ratingOption->getId(), 10);
}

/*
 * Added a sleep because in a few tests the sql query orders by created at. Without the sleep the reviews
 * have sometimes the same created at timestamp, that causes this tests randomly to fail.
 */
sleep(1);

$review = $objectManager->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => '2 filter first review', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    11
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    $storeId
)->setStores(
    [$storeId]
)->save();

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
        ->addOptionVote($ratingOption->getId(), 11);
}

/*
 * Added a sleep because in a few tests the sql query orders by created at. Without the sleep the reviews
 * have sometimes the same created at timestamp, that causes this tests randomly to fail.
 */
sleep(1);

$review = $objectManager->create(
    \Magento\Review\Model\Review::class,
    ['data' => ['nickname' => 'Nickname', 'title' => '1 filter second review', 'detail' => 'Review text']]
);
$review->setEntityId(
    $review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE)
)->setEntityPkValue(
    12
)->setStatusId(
    \Magento\Review\Model\Review::STATUS_APPROVED
)->setStoreId(
    $storeId
)->setStores(
    [$storeId]
)->save();

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
        ->getLastItem();
    $rating->setReviewId($review->getId())
        ->addOptionVote($ratingOption->getId(), 12);
}
