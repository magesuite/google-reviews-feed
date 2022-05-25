<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Review/_files/product_review_with_rating.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/ean_attribute.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/brand.php');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$product->setEan('10000000');
$product->setBrand(600);
$productRepository->save($product);
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$review = $registry->registry('review_data');
$review->aggregate();
