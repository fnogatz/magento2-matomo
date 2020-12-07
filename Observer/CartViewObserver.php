<?php
/**
 * Copyright 2016-2018 Henrik Hedelund
 * Copyright 2020      Falco Nogatz
 *
 * This file is part of Chessio_Matomo.
 *
 * Chessio_Matomo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Chessio_Matomo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Chessio_Matomo.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chessio\Matomo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `controller_action_predispatch_checkout_cart_index'
 *
 */
class CartViewObserver implements ObserverInterface
{

    /**
     * Matomo tracker instance
     *
     * @var \Chessio\Matomo\Model\Tracker
     */
    protected $_matomoTracker;

    /**
     * Tracker helper
     *
     * @var \Chessio\Matomo\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
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
     * @param \Chessio\Matomo\Model\Tracker $matomoTracker
     * @param \Chessio\Matomo\Helper\Tracker $trackerHelper
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     */
    public function __construct(
        \Chessio\Matomo\Model\Tracker $matomoTracker,
        \Chessio\Matomo\Helper\Tracker $trackerHelper,
        \Chessio\Matomo\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession
    ) {
        $this->_matomoTracker = $matomoTracker;
        $this->_trackerHelper = $trackerHelper;
        $this->_dataHelper = $dataHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Push trackEcommerceCartUpdate to tracker on cart view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Chessio\Matomo\Observer\CartViewObserver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $this->_trackerHelper->addQuote(
                $this->_checkoutSession->getQuote(),
                $this->_matomoTracker
            );
        }
        return $this;
    }
}
