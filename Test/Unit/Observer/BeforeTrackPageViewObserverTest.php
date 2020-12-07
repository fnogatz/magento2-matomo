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

namespace Chessio\Matomo\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Chessio\Matomo\Observer\BeforeTrackPageViewObserver
 *
 */
class BeforeTrackPageViewObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Before track page view observer (test subject) instance
     *
     * @var \Chessio\Matomo\Observer\BeforeTrackPageViewObserver $_observer
     */
    protected $_observer;

    /**
     * Matomo data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Matomo tracker mock object
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
        $className = \Chessio\Matomo\Observer\BeforeTrackPageViewObserver::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_observer = $objectManager->getObject($className, $arguments);
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_trackerMock = $this->createPartialMock(
            \Chessio\Matomo\Model\Tracker::class,
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
     * Test for \Chessio\Matomo\Observer\BeforeTrackPageViewObserver::execute
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
