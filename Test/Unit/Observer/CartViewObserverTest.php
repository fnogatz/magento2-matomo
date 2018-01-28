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
 * Test for \Henhed\Piwik\Observer\CartViewObserver
 *
 */
class CartViewObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Cart view observer (test subject) instance
     *
     * @var \Henhed\Piwik\Observer\CartViewObserver
     */
    protected $_observer;

    /**
     * Event observer mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_eventObserverMock;

    /**
     * Tracker mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerMock
     */
    protected $_trackerMock;

    /**
     * Tracker helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerHelperMock
     */
    protected $_trackerHelperMock;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Checkout session mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_checkoutSessionMock
     */
    protected $_checkoutSessionMock;

    /**
     * Quote mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_quoteMock
     */
    protected $_quoteMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $className = \Henhed\Piwik\Observer\CartViewObserver::class;
        $objectManager = new ObjectManager($this);
        $sessionProxyClass = \Magento\Checkout\Model\Session\Proxy::class;
        $arguments = $objectManager->getConstructArguments($className, [
            'checkoutSession' => $this->getMockBuilder($sessionProxyClass)
                ->disableOriginalConstructor()
                ->setMethods(['getQuote'])
                ->getMock()
        ]);
        $this->_observer = $objectManager->getObject($className, $arguments);
        $this->_trackerMock = $arguments['piwikTracker'];
        $this->_trackerHelperMock = $arguments['trackerHelper'];
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_checkoutSessionMock = $arguments['checkoutSession'];
        $this->_eventObserverMock = $this->createMock(
            \Magento\Framework\Event\Observer::class
        );
        $this->_quoteMock = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );
    }

    /**
     * Test for \Henhed\Piwik\Observer\CartViewObserver::execute where
     * tracking is enabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingEnabled()
    {
        // Enable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        // Provide quote mock access from checkout session mock
        $this->_checkoutSessionMock
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Make sure the tracker helpers `addQuote' is called exactly once with
        // provided quote and tracker. Actual behavior of `addQuote' is covered
        // by \Henhed\Piwik\Test\Unit\Helper\TrackerTest.
        $this->_trackerHelperMock
            ->expects($this->once())
            ->method('addQuote')
            ->with($this->_quoteMock, $this->_trackerMock)
            ->willReturn($this->_trackerHelperMock);

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }

    /**
     * Test for \Henhed\Piwik\Observer\CartViewObserver::execute where
     * tracking is disabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingDisabled()
    {
        // Disable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(false);

        // Provide quote mock access from checkout session mock
        $this->_checkoutSessionMock
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Make sure the tracker helpers `addQuote' is never called
        $this->_trackerHelperMock
            ->expects($this->never())
            ->method('addQuote');

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }
}
