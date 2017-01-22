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

namespace Henhed\Piwik\Helper;

use Henhed\Piwik\Model\Tracker as TrackerModel;
use Magento\Quote\Model\Quote;

/**
 * Piwik tracker helper
 *
 * @link http://piwik.org/docs/ecommerce-analytics/
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
}
