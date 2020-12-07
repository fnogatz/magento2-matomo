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
 * @see https://matomo.org/docs/ecommerce-analytics/
 */
class CheckoutSuccessObserver implements ObserverInterface
{

    /**
     * Matomo tracker instance
     *
     * @var \Chessio\Matomo\Model\Tracker $_matomoTracker
     */
    protected $_matomoTracker;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Matomo tracker helper
     *
     * @var \Chessio\Matomo\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Sales order repository
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface $_orderRepository
     */
    protected $_orderRepository;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param \Chessio\Matomo\Model\Tracker $matomoTracker
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param \Chessio\Matomo\Helper\Tracker $trackerHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Chessio\Matomo\Model\Tracker $matomoTracker,
        \Chessio\Matomo\Helper\Data $dataHelper,
        \Chessio\Matomo\Helper\Tracker $trackerHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_matomoTracker = $matomoTracker;
        $this->_dataHelper = $dataHelper;
        $this->_trackerHelper = $trackerHelper;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Push trackEcommerceOrder to tracker on checkout success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Chessio\Matomo\Observer\CheckoutSuccessObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (!$this->_dataHelper->isTrackingEnabled()
            || empty($orderIds) || !is_array($orderIds)
        ) {
            return $this;
        }

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds, 'in')
            ->create();

        $searchResult = $this->_orderRepository->getList($searchCriteria);

        $this->_trackerHelper->addOrders(
            $searchResult->getItems(),
            $this->_matomoTracker
        );

        return $this;
    }
}
