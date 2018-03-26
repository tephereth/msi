<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Setup\Operation;

class CreateShipmentSourceTable
{
    /**#@+
     * ShipmentSource table name
     */
    const TABLE_NAME_SHIPMENT_SOURCE = 'inventory_shipment_source';
    /**#@-*/

    /**
     * Constant for fields in data array
     */
    const SHIPMENT_ID = 'shipment_id';
    const SOURCE_CODE = 'source_code';
}
