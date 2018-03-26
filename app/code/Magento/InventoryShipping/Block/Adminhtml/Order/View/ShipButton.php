<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

class ShipButton extends Container
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //TODO: Need to implement additional logic to check if it's multisource or single
        //TODO: There are two ways": 1. global config for multisource / 2. Check per StockID
        $this->buttonList->update(
            'order_ship',
            'onclick', 'setLocation(\'' . $this->getSourceSelectionUrl() . '\')'
        );
        return $this;
    }

    /**
     * Source Selection URL getter
     *
     * @return string
     */
    public function getSourceSelectionUrl()
    {
        return $this->getUrl(
            'inventoryshipping/order_shipment/SourceSelection',
            [
                'order_id' => $this->getRequest()->getParam('order_id')
            ]
        );
    }
}
