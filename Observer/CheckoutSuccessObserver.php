<?php
/**
 * Copyright 2016-2017 Henrik Hedelund
 *
 * This file is part of Henhed_Piwik.
 *
 * Henhed_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Henhed_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Henhed_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Henhed\Piwik\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `controller_action_predispatch_checkout_cart_index'
 *
 * @link http://piwik.org/docs/ecommerce-analytics/#tracking-ecommerce-orders-items-purchased-required
 */
class CheckoutSuccessObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Henhed\Piwik\Model\Tracker
     */
    protected $_piwikTracker;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Sales order collection factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $_orderCollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\Model\Tracker $piwikTracker
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_dataHelper = $dataHelper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Push trackEcommerceOrder to tracker on checkout success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\CheckoutSuccessObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (!$this->_dataHelper->isTrackingEnabled()
            || empty($orderIds) || !is_array($orderIds)
        ) {
            return $this;
        }

        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        // Group order items by SKU since Piwik doesn't seem to handle multiple
        // `addEcommerceItem' with the same SKU.
        $piwikItems = [];
        // Aggregate all placed orders into one since Piwik seems to only
        // register one `trackEcommerceOrder' per request. (For multishipping)
        $piwikOrder = [];

        foreach ($collection as $order) {
            /* @var $order \Magento\Sales\Model\Order */

            foreach ($order->getAllVisibleItems() as $item) {
                /* @var $item \Magento\Sales\Model\Order\Item */

                $sku   = $item->getSku();
                $name  = $item->getName();
                $price = (float) $item->getBasePriceInclTax();
                $qty   = (float) $item->getQtyOrdered();

                if (!isset($piwikItems[$sku])) {
                    $piwikItems[$sku] = [$sku, $name, $price * $qty, $qty];
                } else {
                    // Aggregate row total instead of unit price in case there
                    // are different prices for the same SKU.
                    $piwikItems[$sku][2] += $price * $qty;
                    $piwikItems[$sku][3] += $qty;
                }
            }

            $orderId    = $order->getIncrementId();
            $grandTotal = (float) $order->getBaseGrandTotal();
            $subTotal   = (float) $order->getBaseSubtotalInclTax();
            $tax        = (float) $order->getBaseTaxAmount();
            $shipping   = (float) $order->getBaseShippingInclTax();
            $discount   = abs((float) $order->getBaseDiscountAmount());

            if (empty($piwikOrder)) {
                $piwikOrder = [
                    $orderId, $grandTotal, $subTotal, $tax, $shipping, $discount
                ];
            } else {
                $piwikOrder[0] .= ', ' . $orderId;
                $piwikOrder[1] += $grandTotal;
                $piwikOrder[2] += $subTotal;
                $piwikOrder[3] += $tax;
                $piwikOrder[4] += $shipping;
                $piwikOrder[5] += $discount;
            }
        }

        // Push `addEcommerceItem'
        foreach ($piwikItems as $piwikItem) {

            list($sku, $name, $rowTotal, $qty) = $piwikItem;

            $this->_piwikTracker->addEcommerceItem(
                $sku,
                $name,
                false,
                ($qty > 0) // div-by-zero protection
                    ? $rowTotal / $qty // restore to unit price
                    : 0,
                $qty
            );
        }

        // Push `trackEcommerceOrder'
        if (!empty($piwikOrder)) {

            list($orderId, $grandTotal, $subTotal, $tax, $shipping, $discount)
                = $piwikOrder;

            $this->_piwikTracker->trackEcommerceOrder(
                $orderId,
                $grandTotal,
                $subTotal,
                $tax,
                $shipping,
                ($discount > 0)
                    ? $discount
                    : false
            );
        }

        return $this;
    }
}
