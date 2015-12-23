<?php
/**
 * Copyright 2015 Henrik Hedelund
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
 */
class CartViewObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Henhed\Piwik\Model\Tracker
     */
    protected $_piwikTracker;

    /**
     * Checkout session instance
     *
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\Model\Tracker $piwikTracker
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Push trackEcommerceCartUpdate to tracker on cart view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\CartViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->_checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $this->_piwikTracker->addEcommerceItem(
                $item->getSku(),
                $item->getName(),
                false,
                (float) $item->getPrice(),
                (float) $item->getQty()
            );
        }

        $this->_piwikTracker->trackEcommerceCartUpdate(
            (float) $quote->getBaseGrandTotal()
        );

        return $this;
    }
}
