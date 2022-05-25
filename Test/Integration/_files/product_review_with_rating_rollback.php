<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/Review/_files/product_review_with_rating_rollback.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/ean_attribute_rollback.php');
$resolver->requireDataFixture('MageSuite_GoogleReviewsFeed::Test/Integration/_files/brand_rollback.php');
