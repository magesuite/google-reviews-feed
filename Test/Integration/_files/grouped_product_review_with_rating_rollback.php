<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Customer/_files/customer_rollback.php');
$resolver->requireDataFixture('Magento/GroupedProduct/_files/product_grouped_with_simple_rollback.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/ean_attribute_rollback.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/brand_rollback.php');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$review = $registry->registry('review_data_grouped');
$review->delete();
$registry->unregister('review_data_grouped');
