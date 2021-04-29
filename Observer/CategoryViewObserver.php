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
 * Observer for `catalog_controller_category_init_after'
 *
 */
class CategoryViewObserver implements ObserverInterface
{

    /**
     * Matomo tracker instance
     *
     * @var \Chessio\Matomo\Model\Tracker
     */
    protected $_matomoTracker;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Chessio\Matomo\Model\Tracker $matomoTracker
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     */
    public function __construct(
        \Chessio\Matomo\Model\Tracker $matomoTracker,
        \Chessio\Matomo\Helper\Data $dataHelper
    ) {
        $this->_matomoTracker = $matomoTracker;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Push EcommerceView to tracker on category view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Chessio\Matomo\Observer\CategoryViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isTrackingEnabled()) {
            return $this;
        }

        $category = $observer->getEvent()->getCategory();
        /** @var \Magento\Catalog\Model\Category $category */

        $this->_matomoTracker->setEcommerceView(
            false,
            false,
            $category->getName()
        );

        return $this;
    }
}
