<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ResourceModel\ShipmentSource;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryShipping\Setup\Operation\CreateShipmentSourceTable;

/**
 * Get source code by shipment Id
 */
class GetSourceCodeByShipmentId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get the source code by shipment Id
     *
     * @param int $shipmentId
     * @return string|null
     */
    public function execute(int $shipmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection
            ->getTableName(CreateShipmentSourceTable::TABLE_NAME_SHIPMENT_SOURCE);

        $select = $connection->select()
            ->from($tableName, [
                CreateShipmentSourceTable::SOURCE_CODE => CreateShipmentSourceTable::SOURCE_CODE
            ])
            ->where(CreateShipmentSourceTable::SHIPMENT_ID . ' = ?', $shipmentId)
            ->limit(1);

        $sourceCode = $connection->fetchOne($select);
        if (!$sourceCode) {
            return null;
        }
        return $sourceCode;
    }
}
