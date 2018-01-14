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
 * Test for \Henhed\Piwik\Observer\BeforeTrackPageViewObserver
 *
 */
class BeforeTrackPageViewObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Before track page view observer (test subject) instance
     *
     * @var \Henhed\Piwik\Observer\BeforeTrackPageViewObserver $_observer
     */
    protected $_observer;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Piwik tracker mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerMock
     */
    protected $_trackerMock;

    /**
     * Event mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventMock
     */
    protected $_eventMock;

    /**
     * Event observer mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_eventObserverMock
     */
    protected $_eventObserverMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $className = \Henhed\Piwik\Observer\BeforeTrackPageViewObserver::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_observer = $objectManager->getObject($className, $arguments);
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_trackerMock = $this->createPartialMock(
            \Henhed\Piwik\Model\Tracker::class,
            ['enableLinkTracking', 'setLinkTrackingTimer']
        );
        $this->_eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getTracker']
        );
        $this->_eventObserverMock = $this->createMock(
            \Magento\Framework\Event\Observer::class
        );
    }

    /**
     * Data provider for `testExecute'
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [true,  500, $this->once(),  $this->once()],
            [true,  0,   $this->once(),  $this->never()],
            [true,  -1,  $this->once(),  $this->never()],
            [false, 500, $this->never(), $this->never()],
            [false, 0,   $this->never(), $this->never()]
        ];
    }

    /**
     * Test for \Henhed\Piwik\Observer\BeforeTrackPageViewObserver::execute
     *
     * @param bool $linkTrackingEnabled
     * @param int $linkTrackingDelay
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $enableLinkTrackingMatcher
     * @param \PHPUnit_Framework_MockObject_Matcher_Invocation $setLinkTrackingTimerMatcher
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $linkTrackingEnabled,
        $linkTrackingDelay,
        $enableLinkTrackingMatcher,
        $setLinkTrackingTimerMatcher
    ) {
        // Prepare observer mock
        $this->_eventObserverMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
        $this->_eventMock
            ->expects($this->once())
            ->method('getTracker')
            ->willReturn($this->_trackerMock);

        // Prepare data helper mock
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isLinkTrackingEnabled')
            ->willReturn($linkTrackingEnabled);
        $this->_dataHelperMock
            ->expects($this->any())
            ->method('getLinkTrackingDelay')
            ->willReturn($linkTrackingDelay);

        // Prepare tracker mock
        $this->_trackerMock
            ->expects($enableLinkTrackingMatcher)
            ->method('enableLinkTracking')
            ->with(true)
            ->willReturn($this->_trackerMock);
        $this->_trackerMock
            ->expects($setLinkTrackingTimerMatcher)
            ->method('setLinkTrackingTimer')
            ->with($linkTrackingDelay)
            ->willReturn($this->_trackerMock);

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }
}
