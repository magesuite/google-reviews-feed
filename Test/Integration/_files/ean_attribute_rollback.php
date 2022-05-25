<?php
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
$attribute = $eavConfig->getAttribute('catalog_product', 'ean');

if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute && $attribute->getId()) {
    $attribute->delete();
}

$eavConfig->clear();
