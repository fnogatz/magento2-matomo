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
 * Test for \Chessio\Matomo\Observer\CategoryViewObserver
 *
 */
class CategoryViewObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Category view observer (test subject) instance
     *
     * @var \Chessio\Matomo\Observer\CategoryViewObserver $_observer
     */
    protected $_observer;

    /**
     * Matomo tracker mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_trackerMock
     */
    protected $_trackerMock;

    /**
     * Matomo data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

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
     * Category model mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_categoryMock
     */
    protected $_categoryMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $className = \Chessio\Matomo\Observer\CategoryViewObserver::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_trackerMock = $this->createPartialMock(
            \Chessio\Matomo\Model\Tracker::class,
            ['setEcommerceView']
        );
        $arguments['matomoTracker'] = $this->_trackerMock;
        $this->_observer = $objectManager->getObject($className, $arguments);
        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getCategory']
        );
        $this->_eventObserverMock = $this->createMock(
            \Magento\Framework\Event\Observer::class
        );
        $this->_categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getName']
        );
    }

    /**
     * Test for \Chessio\Matomo\Observer\CategoryViewObserver::execute when Matomo
     * tracking is enabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingEnabled()
    {
        $categoryName = 'Some category name';

        // Prepare mock objects
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);
        $this->_eventObserverMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
        $this->_eventMock
            ->expects($this->once())
            ->method('getCategory')
            ->willReturn($this->_categoryMock);
        $this->_categoryMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn($categoryName);

        // Make sure trackers' `setEcommerceView' is called exactly once
        $this->_trackerMock
            ->expects($this->once())
            ->method('setEcommerceView')
            ->with(false, false, $categoryName)
            ->willReturn($this->_trackerMock);

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }

    /**
     * Test for \Chessio\Matomo\Observer\CategoryViewObserver::execute when Matomo
     * tracking is disabled.
     *
     * @return void
     */
    public function testExecuteWithTrackingDisabled()
    {
        // Prepare mock objects
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(false);
        $this->_eventObserverMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
        $this->_eventMock
            ->expects($this->any())
            ->method('getCategory')
            ->willReturn($this->_categoryMock);

        // Make sure trackers' `setEcommerceView' is never called
        $this->_trackerMock
            ->expects($this->never())
            ->method('setEcommerceView');

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }
}
