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
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Add `trackEcommerceCartUpdate' checkout cart customer data
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        $result['piwikActions'] = [];
        $quote = $this->_checkoutSession->getQuote();

        foreach ($quote->getAllVisibleItems() as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            $result['piwikActions'][] = [
                'addEcommerceItem',
                $item->getSku(),
                $item->getName(),
                false,
                (float) $item->getPrice(),
                (float) $item->getQty()
            ];
        }

        $result['piwikActions'][] = [
            'trackEcommerceCartUpdate',
            (float) $quote->getBaseGrandTotal()
        ];

        return $result;
    }
}
