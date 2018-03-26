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
 * Save Shipment Source
 */
class SaveShipmentSource
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $shipmentId
     * @param string $sourceCode
     * @return void
     */
    public function execute(int $shipmentId, string $sourceCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateShipmentSourceTable::TABLE_NAME_SHIPMENT_SOURCE);

        $data = [
            CreateShipmentSourceTable::SHIPMENT_ID => $shipmentId,
            CreateShipmentSourceTable::SOURCE_CODE => $sourceCode
        ];

        $connection->insertOnDuplicate($tableName, $data, [CreateShipmentSourceTable::SOURCE_CODE]);
    }
}
