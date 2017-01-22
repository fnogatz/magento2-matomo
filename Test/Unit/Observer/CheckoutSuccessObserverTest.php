<?php
/**
 * Copyright 2016-2017 Henrik Hedelund
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
class CheckoutSuccessObserverTest extends \PHPUnit_Framework_TestCase
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
     * @var \Henhed\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Order collection mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_orderCollectionMock
     */
    protected $_orderCollectionMock;

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

        // Create tracker
        $trackerClass = 'Henhed\Piwik\Model\Tracker';
        $trackerArgs = $objectMgr->getConstructArguments($trackerClass, [
            'actionFactory' => $this->getMock(
                'Henhed\Piwik\Model\Tracker\ActionFactory',
                ['create'], [], '', false
            )
        ]);
        $trackerArgs['actionFactory']
            ->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                return new \Henhed\Piwik\Model\Tracker\Action(
                    $data['name'],
                    $data['args']
                );
            });
        $this->_tracker = $objectMgr->getObject($trackerClass, $trackerArgs);

        // Create test subject
        $className = 'Henhed\Piwik\Observer\CheckoutSuccessObserver';
        $arguments = $objectMgr->getConstructArguments($className, [
            'orderCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\CollectionFactory',
                ['create'], [], '', false
            )
        ]);
        $arguments['piwikTracker'] = $this->_tracker;
        $this->_testSubject = $objectMgr->getObject($className, $arguments);
        $this->_dataHelperMock = $arguments['dataHelper'];

        // Create event observer mock objects
        $this->_orderCollectionMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Collection',
            [], [], '', false
        );
        $arguments['orderCollectionFactory']
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->_orderCollectionMock);
        $this->_eventMock = $this->getMock(
            'Magento\Framework\Event', ['getOrderIds'], [], '', false
        );
        $this->_eventObserverMock = $this->getMock(
            'Magento\Framework\Event\Observer', [], [], '', false
        );
        $this->_eventObserverMock
            ->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->_eventMock);
    }

    /**
     * Create order item mock object
     *
     * @param string $sku
     * @param string $name
     * @param float $price
     * @param float $qty
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderItemMock($sku, $name, $price, $qty)
    {
        $methodMap = [
            'getSku'              => $sku,
            'getName'             => $name,
            'getBasePriceInclTax' => $price,
            'getQtyOrdered'       => $qty
        ];
        $itemMock = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            array_keys($methodMap),
            [],
            '',
            false
        );
        foreach ($methodMap as $method => $returnValue) {
            $itemMock
                ->expects($this->any())
                ->method($method)
                ->willReturn($returnValue);
        }
        return $itemMock;
    }

    /**
     * Create order mock object
     *
     * @param string $incrementId
     * @param float $grandTotal
     * @param float $subTotal
     * @param float $tax
     * @param float $shipping
     * @param float $discount
     * @param array $itemsData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getOrderMock($incrementId, $grandTotal, $subTotal, $tax,
        $shipping, $discount, $itemsData
    ) {
        $items = [];
        foreach ($itemsData as $itemData) {
            list($sku, $name, $price, $qty) = $itemData;
            $items[] = $this->_getOrderItemMock($sku, $name, $price, $qty);
        }
        $methodMap = [
            'getIncrementId'         => $incrementId,
            'getBaseGrandTotal'      => $grandTotal,
            'getBaseSubtotalInclTax' => $subTotal,
            'getBaseTaxAmount'       => $tax,
            'getBaseShippingInclTax' => $shipping,
            'getBaseDiscountAmount'  => $discount,
            'getAllVisibleItems'     => $items
        ];
        $orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            array_keys($methodMap),
            [],
            '',
            false
        );
        foreach ($methodMap as $method => $returnValue) {
            $orderMock
                ->expects($this->any())
                ->method($method)
                ->willReturn($returnValue);
        }
        return $orderMock;
    }

    /**
     * Order collection data provider
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                // Sample orders data
                [
                    [
                        1, '100001', 123.45, 101, 10, 15, -5,
                        [
                            ['sku1', 'Name 1', 50, 2],
                            ['sku2', 'Name 2', 50, 3]
                        ]
                    ],
                    [
                        '2', '100002', '234.56', '201', '15', '20', '-50',
                        [
                            ['sku2', 'Name 2 (2)', '60', '1'],
                            ['sku3', 'Name 3',     '70', '2']
                        ]
                    ]
                ],
                // Expected tracker data
                [
                    // Same as `sku1' from `100001'
                    ['addEcommerceItem', 'sku1', 'Name 1', false, 50.0, 2.0],

                    // Aggregated data for `sku2' from `100001' *and* `100002'
                    [
                        'addEcommerceItem',
                        'sku2',
                        'Name 2', // Name from first occurance of `sku2'
                        false,    // No category name
                        52.5,     // Sum of price / sum of qty (50*3 + 60)/4
                        4.0       // Sum of qty
                    ],

                    // Same as `sku3' from `100002'
                    ['addEcommerceItem', 'sku3', 'Name 3', false, 70.0, 2.0],

                    // Aggregated order data from `100001' and `100002'
                    [
                        'trackEcommerceOrder',
                        '100001, 100002', // Concat increment IDs
                        358.01,           // 123.45 + 234.56
                        302.0,            // 101 + 201
                        25.0,             // 10 + 15
                        35.0,             // 15 + 20
                        55.0              // abs(-5 + -50)
                    ]
                ]
            ]
        ];
    }

    /**
     * Test for \Henhed\Piwik\Observer\CheckoutSuccessObserver::execute where
     * tracking is enabled.
     *
     * @param array $ordersData
     * @param array $expectedResult
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecuteWithTrackingEnabled($ordersData, $expectedResult)
    {
        $orderIds = [];
        $orders = [];
        foreach ($ordersData as $orderData) {
            list($entityId, $incrementId, $grandTotal, $subTotal, $tax,
                 $shipping, $discount, $itemsData) = $orderData;
            $orderIds[] = $entityId;
            $orders[] = $this->_getOrderMock($incrementId, $grandTotal,
                                             $subTotal, $tax, $shipping,
                                             $discount, $itemsData);
        }

        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        $this->_eventMock
            ->expects($this->atLeastOnce())
            ->method('getOrderIds')
            ->willReturn($orderIds);

        $this->_orderCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($orders));

        $this->assertSame(
            $this->_testSubject,
            $this->_testSubject->execute($this->_eventObserverMock)
        );
        $this->assertEquals($expectedResult, $this->_tracker->toArray());
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

        $this->_orderCollectionMock
            ->expects($this->never())
            ->method('getIterator');

        $this->assertSame(
            $this->_testSubject,
            $this->_testSubject->execute($this->_eventObserverMock)
        );

        $this->assertEquals([], $this->_tracker->toArray());
    }
}
