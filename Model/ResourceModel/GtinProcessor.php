<?php
declare(strict_types=1);

namespace MageSuite\GoogleReviewsFeed\Model\ResourceModel;

class GtinProcessor
{
    protected \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration;

    protected \Magento\Eav\Model\Config $eavConfig;

    public function __construct(
        \MageSuite\GoogleReviewsFeed\Helper\Configuration $configuration,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->configuration = $configuration;
        $this->eavConfig = $eavConfig;
    }

    /**
     * GTIN is required in the product feed
     */
    public function execute(\Magento\Framework\DB\Select $select, string $fieldName = 'product_id'): \Magento\Framework\DB\Select
    {
        $attributeCode = $this->configuration->getGtinAttribute();
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        if (!$attribute->getId()) {
            return $select;
        }

        $conditions = [
            "main_table.{$fieldName} = attr_table.entity_id",
            'attr_table.attribute_id = ' . $attribute->getId(),
            'attr_table.value IS NOT NULL'
        ];
        $select->join(
            ['attr_table' => $attribute->getBackendTable()],
            implode(' AND ', $conditions),
            []
        );

        return $select;
    }
}
