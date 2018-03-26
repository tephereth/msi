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
 * Delete Shipment Source
 */
class DeleteShipmentSource
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
     * @return void
     */
    public function execute(int $shipmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(CreateShipmentSourceTable::TABLE_NAME_SHIPMENT_SOURCE);

        $connection->delete($tableName, [
            CreateShipmentSourceTable::SHIPMENT_ID . ' = ?' => $shipmentId,
        ]);
    }
}
