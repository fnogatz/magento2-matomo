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

namespace Henhed\Piwik\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Henhed\Piwik\Observer\CheckoutSuccessObserver
 *
 */
class CheckoutSuccessObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Checkout success observer (test subject) instance
     *
     * @var \Henhed\Piwik\Observer\CheckoutSuccessObserver $_testSubject
     */
    protected $_testSubject;

    /**
     * Tracker instance
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerMock
     */
    protected $_trackerMock;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Piwik tracker helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerHelperMock
     */
    protected $_trackerHelperMock;

    /**
     * Sales order repository mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_orderRepositoryMock
     */
    protected $_orderRepositoryMock;

    /**
     * Search criteria builder mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_searchCriteriaBuilderMock
     */
    protected $_searchCriteriaBuilderMock;

    /**
     * Event observer mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_eventObserverMock;

    /**
     * Event mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventMock
     */
    protected $_eventMock;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $objectMgr = new ObjectManager($this);

        // Create test subject
        $className = \Henhed\Piwik\Observer\CheckoutSuccessObserver::class;
        $arguments = $objectMgr->getConstructArguments($className);
        $this->_testSubject = $objectMgr->getObject($className, $arguments);
        $this->_trackerMock = $arguments['piwikTracker'];
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_trackerHelperMock = $arguments['trackerHelper'];
        $this->_orderRepositoryMock = $arguments['orderRepository'];
        $this->_searchCriteriaBuilderMock = $arguments['searchCriteriaBuilder'];

        $this->_eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getOrderIds']
        );
        $this->_eventObserverMock = $this->createMock(
            \Magento\Framework\Event\Observer::class
        );
        $this->_eventObserverMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
    }

    /**
     * Test for \Henhed\Piwik\Observer\CheckoutSuccessObserver::execute where
     * tracking is enabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingEnabled()
    {
        $orders = [
            1 => new \stdClass(),
            2 => new \stdClass()
        ];

        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        $this->_eventMock
            ->expects($this->atLeastOnce())
            ->method('getOrderIds')
            ->willReturn(array_keys($orders));

        $this->_searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('addFilter')
            ->with('entity_id', array_keys($orders), 'in')
            ->willReturn($this->_searchCriteriaBuilderMock);

        $searchCriteriaMock = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaInterface::class
        );

        $this->_searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createConfiguredMock(
            \Magento\Sales\Api\Data\OrderSearchResultInterface::class,
            ['getItems' => $orders]
        );

        $this->_orderRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $this->_trackerHelperMock
            ->expects($this->once())
            ->method('addOrders')
            ->with($orders, $this->_trackerMock)
            ->willReturn($this->_trackerHelperMock);

        $this->assertSame(
            $this->_testSubject,
            $this->_testSubject->execute($this->_eventObserverMock)
        );
    }

    /**
     * Test for \Henhed\Piwik\Observer\CheckoutSuccessObserver::execute where
     * tracking is disabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingDisabled()
    {
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(false);

        $this->_eventMock
            ->expects($this->any())
            ->method('getOrderIds')
            ->willReturn([1]);

        $this->_searchCriteriaBuilderMock
            ->expects($this->never())
            ->method('addFilter');

        $this->_searchCriteriaBuilderMock
            ->expects($this->never())
            ->method('create');

        $this->_orderRepositoryMock
            ->expects($this->never())
            ->method('getList');

        $this->_trackerHelperMock
            ->expects($this->never())
            ->method('addOrders');

        $this->assertSame(
            $this->_testSubject,
            $this->_testSubject->execute($this->_eventObserverMock)
        );
    }
}
