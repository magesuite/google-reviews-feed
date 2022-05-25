<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$brandRepository = $objectManager->create(\MageSuite\BrandManagement\Api\BrandsRepositoryInterface::class);
$brand = $objectManager->create(\MageSuite\BrandManagement\Model\Brands::class);
$brand->setEntityId(600)
    ->setStoreId(0)
    ->setUrlKey('url/key')
    ->setLayoutUpdateXml('layout update xml')
    ->setBrandName('Example Brand')
    ->setEnabled(1)
    ->setIsFeatured(1)
    ->setBrandIcon('testimage.png')
    ->setBrandAdditionalIcon('testimage_additional.png')
    ->setMetaTitle('Test meta title')
    ->setMetaDescription('Test meta description')
    ->setMetaRobots('NOINDEX,NOFOLLOW');
$brandRepository->save($brand);
