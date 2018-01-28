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

namespace Henhed\Piwik\CustomerData\Checkout;

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
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Tracker helper
     *
     * @var \Henhed\Piwik\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Tracker factory
     *
     * @var \Henhed\Piwik\Model\TrackerFactory $_trackerFactory
     */
    protected $_trackerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Henhed\Piwik\Helper\Tracker $trackerHelper
     * @param \Henhed\Piwik\Model\TrackerFactory $trackerFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Henhed\Piwik\Helper\Tracker $trackerHelper,
        \Henhed\Piwik\Model\TrackerFactory $trackerFactory
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
                $result['piwikActions'] = $tracker->toArray();
            }
        }
        return $result;
    }
}
