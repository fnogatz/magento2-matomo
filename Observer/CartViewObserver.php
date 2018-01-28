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
     * Tracker helper
     *
     * @var \Henhed\Piwik\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

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
     * @param \Henhed\Piwik\Helper\Tracker $trackerHelper
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Henhed\Piwik\Helper\Tracker $trackerHelper,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_trackerHelper = $trackerHelper;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Push trackEcommerceCartUpdate to tracker on cart view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\CartViewObserver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $this->_trackerHelper->addQuote(
                $this->_checkoutSession->getQuote(),
                $this->_piwikTracker
            );
        }
        return $this;
    }
}
