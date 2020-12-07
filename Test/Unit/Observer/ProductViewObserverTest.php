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
 * Test for \Chessio\Matomo\Observer\ProductViewObserver
 *
 */
class ProductViewObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Product view observer (test subject) instance
     *
     * @var \Chessio\Matomo\Observer\ProductViewObserver $_observer
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
     * Product model mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_productMock
     */
    protected $_productMock;

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
        $className = \Chessio\Matomo\Observer\ProductViewObserver::class;
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
            ['getProduct']
        );
        $this->_eventObserverMock = $this->createMock(
            \Magento\Framework\Event\Observer::class
        );
        $this->_productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getCategory', 'getSku', 'getName', 'getFinalPrice']
        );
        $this->_categoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Category::class,
            ['getName']
        );
    }

    /**
     * Data provicer for `testExecute'
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['sku1', 'Product Name #1', 123.45],
            ['sku2', 'Product Name #2', '234.56', 'Categor Name #1']
        ];
    }

    /**
     * Test for \Chessio\Matomo\Observer\ProductViewObserver::execute where
     * tracking is enabled.
     *
     * @param string $sku
     * @param string $name
     * @param float $price
     * @param string|null $category
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecuteWithTrackingEnabled(
        $sku,
        $name,
        $price,
        $category = null
    ) {
        // Enable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        // Prepare event observer mock
        $this->_eventObserverMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
        $this->_eventMock
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->_productMock);

        // Prepare product mock
        $methodMap = [
            'getSku'        => $sku,
            'getName'       => $name,
            'getFinalPrice' => $price
        ];
        foreach ($methodMap as $method => $returnValue) {
            $this->_productMock
                ->expects($this->once())
                ->method($method)
                ->willReturn($returnValue);
        }

        // Prepare category mock if category name was provided
        if ($category !== null) {
            $this->_productMock
                ->expects($this->once())
                ->method('getCategory')
                ->willReturn($this->_categoryMock);
            $this->_categoryMock
                ->expects($this->once())
                ->method('getName')
                ->willReturn($category);
        }

        // Make sure trackers' `setEcommerceView' is called exactly once.
        $this->_trackerMock
            ->expects($this->once())
            ->method('setEcommerceView')
            ->with(
                $sku,
                $name,
                // Category should be FALSE if product has no category
                ($category === null) ? false : $category,
                (float) $price
            )
            ->willReturn($this->_trackerMock);

        // Assert that `execute' returns $this
        $this->assertSame(
            $this->_observer,
            $this->_observer->execute($this->_eventObserverMock)
        );
    }

    /**
     * Test for \Chessio\Matomo\Observer\ProductViewObserver::execute where
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

        // Provide access to event and product though they should preferably
        // never be touched when tracking is disabled.
        $this->_eventObserverMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
        $this->_eventMock
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->_productMock);

        // Make sure trackers' `setEcommerceView' is never called.
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
