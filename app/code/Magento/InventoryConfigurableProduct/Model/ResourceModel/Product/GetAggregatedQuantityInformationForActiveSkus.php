<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model\ResourceModel\Product;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class GetAggregatedQuantityInformationForActiveSkus
{
    /**
     * Constants for represent fields in result array
     */
    const QUANTITY = 'quantity';
    const IS_SALABLE = 'is_salable';
    /**#@-*/

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $indexTableNameResolver;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StockIndexTableNameResolverInterface $indexTableNameResolver
     * @param StockResolverInterface $stockResolver
     * @param AttributeRepositoryInterface $attributeRepository
     * @param MetadataPool $metadataPool
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockIndexTableNameResolverInterface $indexTableNameResolver,
        StockResolverInterface $stockResolver,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool,
        WebsiteRepositoryInterface $websiteRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexTableNameResolver = $indexTableNameResolver;
        $this->stockResolver = $stockResolver;
        $this->attributeRepository = $attributeRepository;
        $this->metadataPool = $metadataPool;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param ProductInterface[] $productList
     * @param SalesChannelInterface $salesChannel
     * @return array
     * @throws Exception
     * @throws NoSuchEntityException
     */
    public function execute(array $productList, SalesChannelInterface $salesChannel): array
    {
        $stock = $this->stockResolver->get($salesChannel->getType(), $salesChannel->getCode());
        $stockTableName = $this->indexTableNameResolver->execute((int)$stock->getStockId());
        $connection = $this->resourceConnection->getConnection();

        $website = $this->websiteRepository->get($salesChannel->getCode());
        $storeGroup = $this->groupRepository->get($website->getDefaultGroupId());

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $statusAttribute = $this->attributeRepository->get(Product::ENTITY, 'status');

        $expression = $connection->getCheckSql(
            'at_status_store.value_id > 0',
            'at_status_store.value',
            'at_status_default.value'
        );

        $select = $connection->select()
            ->from(
                ['stock' => $stockTableName],
                [
                    GetAggregatedQuantityInformationForActiveSkus::QUANTITY => 'SUM(stock.quantity)',
                    GetAggregatedQuantityInformationForActiveSkus::IS_SALABLE => 'MAX(stock.is_salable)',
                ]
            )
            ->joinInner(
                ['product' => $connection->getTableName('catalog_product_entity')],
                'product.sku = stock.sku',
                []
            )->joinLeft(
                ['at_status_default' => $connection->getTableName('catalog_product_entity_int')],
                'at_status_default.attribute_id = ' . $statusAttribute->getAttributeId()
                . ' AND at_status_default.store_id = 0'
                . ' AND at_status_default.' . $linkField . ' = product.' . $linkField,
                []
            )->joinLeft(
                ['at_status_store' => $connection->getTableName('catalog_product_entity_int')],
                'at_status_store.attribute_id = ' . $statusAttribute->getAttributeId()
                . ' AND at_status_store.store_id = ' . $storeGroup->getDefaultStoreId()
                . ' AND at_status_store.' . $linkField . ' = product.' . $linkField,
                []
            )->where(
                $connection->quoteInto($expression . ' = ?', 1)
            )->where(
                'stock.sku IN (?)',
                $this->getSkuList($productList)
            );

        return $connection->fetchAll($select);
    }

    /**
     * @param ProductInterface[] $productList
     * @return array
     */
    private function getSkuList(array $productList)
    {
        $skuList = [];
        foreach ($productList as $product) {
            $skuList[] = $product->getSku();
        }
        return $skuList;
    }
}
