<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;

class CollectSourcesForShipmentItems
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $shipmentExtensionFactory;

    /**
     * CollectSourcesForShipmentItems constructor.
     * @param ShipmentExtensionFactory $shipmentExtensionFactory
     */
    public function __construct(
        ShipmentExtensionFactory $shipmentExtensionFactory
    ) {
        $this->shipmentExtensionFactory = $shipmentExtensionFactory;
    }

    /**
     * @param ShipmentFactory $subject
     * @param callable $proceed
     * @param Order $order
     * @param array $items
     * @param null $tracks
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function aroundCreate(
        ShipmentFactory $subject,
        callable $proceed,
        Order $order,
        array $items = [],
        $tracks = null
    ) {
        //TODO: Used for test.. Most likely this plugin will be deleted
        $shipment =  $proceed($order, $items, $tracks);
        $shipmentExtension = $shipment->getExtensionAttributes();

        if (empty($shipmentExtension)) {
            $shipmentExtension = $this->shipmentExtensionFactory->create();
            $shipmentExtension->setSourceCode('default');
            $shipment->setExtensionAttributes($shipmentExtension);
        }

        return $shipment;
    }
}
