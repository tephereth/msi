<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model\IsProductSalableCondition;

use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\InventoryCatalog\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurableProduct\Model\ResourceModel\Product\GetAggregatedQuantityInformationForActiveSkus;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Condition for configurable products.
 */
class HasSalableVariationsCondition implements IsProductSalableInterface
{
    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var GetAggregatedQuantityInformationForActiveSkus
     */
    private $getAggregatedQuantityInformationForActiveSkus;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param LinkManagementInterface $linkManagement
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param GetAggregatedQuantityInformationForActiveSkus $getAggregatedQuantityInformationForActiveSkus
     */
    public function __construct(
        LinkManagementInterface $linkManagement,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        GetAggregatedQuantityInformationForActiveSkus $getAggregatedQuantityInformationForActiveSkus
    ) {
        $this->linkManagement = $linkManagement;
        $this->getAggregatedQuantityInformationForActiveSkus = $getAggregatedQuantityInformationForActiveSkus;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, SalesChannelInterface $salesChannel): bool
    {
        $type = $this->getProductTypesBySkus->execute([$sku])[$sku];
        if ($type !== Configurable::TYPE_CODE) {
            return true;
        }

        $variations = $this->linkManagement->getChildren($sku);
        $quantityInformation = $this->getAggregatedQuantityInformationForActiveSkus
            ->execute($variations, $salesChannel);
        return (bool)$quantityInformation[GetAggregatedQuantityInformationForActiveSkus::IS_SALABLE] ?? false;
    }
}
