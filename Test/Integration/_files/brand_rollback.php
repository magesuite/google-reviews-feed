<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$brandRepository = $objectManager->create(\MageSuite\BrandManagement\Api\BrandsRepositoryInterface::class);

try {
    $brand = $brandRepository->getById(600);

    if ($brand) {
        $brandRepository->delete($brand);
    }
} catch (\Exception $e) {
}
