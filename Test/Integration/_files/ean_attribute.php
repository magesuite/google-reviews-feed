<?php
declare(strict_types=1);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$eavSetup = $objectManager->create(\Magento\Eav\Setup\EavSetup::class);
$eavSetup->addAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'ean',
    [
        'group' => 'General',
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => 'Ean',
        'input' => 'text',
        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'searchable' => false,
        'filterable' => false,
        'comparable' => false,
        'visible_on_front' => false,
        'used_in_product_listing' => true,
        'unique' => false,
        'apply_to' => ''
    ]
);

$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$eavConfig->clear();
