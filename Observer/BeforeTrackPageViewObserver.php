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
 * Observer for `matomo_track_page_view_before'
 *
 */
class BeforeTrackPageViewObserver implements ObserverInterface
{

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     */
    public function __construct(\Chessio\Matomo\Helper\Data $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Push additional actions to tracker before `trackPageView' is added
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Chessio\Matomo\Observer\BeforeTrackPageViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $tracker = $observer->getEvent()->getTracker();
        /** @var \Chessio\Matomo\Model\Tracker $tracker */

        $this->_pushLinkTracking($tracker);

        return $this;
    }

    /**
     * Push link tracking options to given tracker
     *
     * @param \Chessio\Matomo\Model\Tracker $tracker
     * @return \Chessio\Matomo\Observer\BeforeTrackPageViewObserver
     */
    protected function _pushLinkTracking(\Chessio\Matomo\Model\Tracker $tracker)
    {
        if ($this->_dataHelper->isContainerEnabled()) {
            return $this;
        }

        if ($this->_dataHelper->isLinkTrackingEnabled()) {
            $tracker->enableLinkTracking(true);
            $delay = $this->_dataHelper->getLinkTrackingDelay();
            if ($delay > 0) {
                $tracker->setLinkTrackingTimer($delay);
            }
        }
        return $this;
    }
}
