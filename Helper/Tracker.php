<?php
/**
 * Copyright 2016-2018 Henrik Hedelund
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

namespace Henhed\Piwik\Helper;

use Henhed\Piwik\Model\Tracker as TrackerModel;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Piwik tracker helper
 *
 * @see http://piwik.org/docs/ecommerce-analytics/
 */
class Tracker extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Push `addEcommerceItem' with quote item data to given tracker
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Henhed\Piwik\Model\Tracker $tracker
     * @return \Henhed\Piwik\Helper\Tracker
     */
    public function addQuoteItem(Quote\Item $item, TrackerModel $tracker)
    {
        $tracker->addEcommerceItem(
            $item->getSku(),
            $item->getName(),
            false,
            $item->hasCustomPrice()
                ? (float) $item->getCustomPrice()
                : (float) $item->getBasePriceInclTax(),
            (float) $item->getQty()
        );
        return $this;
    }

    /**
     * Push `trackEcommerceCartUpdate' with quote data to given tracker
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Henhed\Piwik\Model\Tracker $tracker
     * @return \Henhed\Piwik\Helper\Tracker
     */
    public function addQuoteTotal(Quote $quote, TrackerModel $tracker)
    {
        $tracker->trackEcommerceCartUpdate((float) $quote->getBaseGrandTotal());
        return $this;
    }

    /**
     * Push quote contents to given tracker
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Henhed\Piwik\Model\Tracker $tracker
     * @return \Henhed\Piwik\Helper\Tracker
     */
    public function addQuote(Quote $quote, TrackerModel $tracker)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            $this->addQuoteItem($item, $tracker);
        }
        $this->addQuoteTotal($quote, $tracker);
        return $this;
    }

    /**
     * Push order contents to given tracker
     *
     * @param \Magento\Sales\Api\Data\OrderInterface[]|\Traversable $orders
     * @param \Henhed\Piwik\Model\Tracker $tracker
     * @return \Henhed\Piwik\Helper\Tracker
     */
    public function addOrders($orders, TrackerModel $tracker)
    {
        $piwikItems = [];
        $piwikOrder = [];

        // Collect tracking data
        foreach ($orders as $order) {
            foreach ($order->getItems() as $item) {
                if (!$item->getParentItemId()) {
                    $this->_appendOrderItemData($item, $piwikItems);
                }
            }
            $this->_appendOrderData($order, $piwikOrder);
        }

        // Push `addEcommerceItem'
        foreach ($piwikItems as $piwikItem) {
            list($sku, $name, $rowTotal, $qty) = $piwikItem;

            $tracker->addEcommerceItem(
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

            $tracker->trackEcommerceOrder(
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

    /**
     * @param OrderItemInterface $item
     * @param array &$data
     * @return void
     */
    protected function _appendOrderItemData(OrderItemInterface $item, &$data)
    {
        $sku   = $item->getSku();
        $name  = $item->getName();
        $price = (float) $item->getBasePriceInclTax();
        $qty   = (float) $item->getQtyOrdered();

        // Group order items by SKU since Piwik doesn't seem to handle multiple
        // `addEcommerceItem' with the same SKU.
        if (!isset($data[$sku])) {
            $data[$sku] = [$sku, $name, $price * $qty, $qty];
        } else {
            // Aggregate row total instead of unit price in case there
            // are different prices for the same SKU.
            $data[$sku][2] += $price * $qty;
            $data[$sku][3] += $qty;
        }
    }

    /**
     * @param OrderInterface $order
     * @param array &$data
     * @return void
     */
    protected function _appendOrderData(OrderInterface $order, &$data)
    {
        $orderId    = $order->getIncrementId();
        $grandTotal = (float) $order->getBaseGrandTotal();
        $subTotal   = (float) $order->getBaseSubtotalInclTax();
        $tax        = (float) $order->getBaseTaxAmount();
        $shipping   = (float) $order->getBaseShippingInclTax();
        $discount   = abs((float) $order->getBaseDiscountAmount());

        // Aggregate all placed orders into one since Piwik seems to only
        // register one `trackEcommerceOrder' per request. (For multishipping)
        if (empty($data)) {
            $data = [
                $orderId, $grandTotal, $subTotal, $tax, $shipping, $discount
            ];
        } else {
            $data[0] .= ', ' . $orderId;
            $data[1] += $grandTotal;
            $data[2] += $subTotal;
            $data[3] += $tax;
            $data[4] += $shipping;
            $data[5] += $discount;
        }
    }
}
