<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Catalog/_files/multiple_products.php');
$objectManager =  \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
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
    $objectManager->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        $objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();
$review->aggregate();

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
    $objectManager->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        $objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();
$review->aggregate();

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
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setStores(
    [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId()
    ]
)->save();
$review->aggregate();
