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
 * @see http://piwik.org/docs/ecommerce-analytics/#tracking-ecommerce-orders-items-purchased-required
 */
class CheckoutSuccessObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Henhed\Piwik\Model\Tracker $_piwikTracker
     */
    protected $_piwikTracker;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Piwik tracker helper
     *
     * @var \Henhed\Piwik\Helper\Tracker $_trackerHelper
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
     * @param \Henhed\Piwik\Model\Tracker $piwikTracker
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Henhed\Piwik\Helper\Tracker $trackerHelper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Henhed\Piwik\Helper\Tracker $trackerHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_dataHelper = $dataHelper;
        $this->_trackerHelper = $trackerHelper;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Push trackEcommerceOrder to tracker on checkout success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\CheckoutSuccessObserver
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
            $this->_piwikTracker
        );

        return $this;
    }
}
