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

namespace Chessio\Matomo\CustomerData\Checkout;

/**
 * Plugin for \Magento\Checkout\CustomerData\Cart
 *
 */
class CartPlugin
{

    /**
     * Checkout session instance
     *
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Tracker helper
     *
     * @var \Chessio\Matomo\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Tracker factory
     *
     * @var \Chessio\Matomo\Model\TrackerFactory $_trackerFactory
     */
    protected $_trackerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param \Chessio\Matomo\Helper\Tracker $trackerHelper
     * @param \Chessio\Matomo\Model\TrackerFactory $trackerFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Chessio\Matomo\Helper\Data $dataHelper,
        \Chessio\Matomo\Helper\Tracker $trackerHelper,
        \Chessio\Matomo\Model\TrackerFactory $trackerFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
        $this->_trackerHelper = $trackerHelper;
        $this->_trackerFactory = $trackerFactory;
    }

    /**
     * Add `trackEcommerceCartUpdate' checkout cart customer data
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $quote = $this->_checkoutSession->getQuote();
            if ($quote->getId()) {
                $tracker = $this->_trackerFactory->create();
                $this->_trackerHelper->addQuote($quote, $tracker);
                $result['matomoActions'] = $tracker->toArray();
            }
        }
        return $result;
    }
}
