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

namespace Henhed\Piwik\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Henhed\Piwik\Helper\Tracker
 *
 */
class TrackerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Piwik tracker helper (test subject) instance
     *
     * @var \Henhed\Piwik\Helper\Tracker $_helper
     */
    protected $_helper;

    /**
     * Tracker instance
     *
     * @var \Henhed\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        // Create test subject
        $this->_helper = $objectManager->getObject(
            \Henhed\Piwik\Helper\Tracker::class
        );

        // Create tracker instance
        $class = \Henhed\Piwik\Model\Tracker::class;
        $arguments = $objectManager->getConstructArguments($class, [
            'actionFactory' => $this->createPartialMock(
                \Henhed\Piwik\Model\Tracker\ActionFactory::class,
                ['create']
            )
        ]);
        $arguments['actionFactory']
            ->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                return new \Henhed\Piwik\Model\Tracker\Action(
                    $data['name'],
                    $data['args']
                );
            });
        $this->_tracker = $objectManager->getObject($class, $arguments);
    }

    /**
     * Quote item data provider
     *
     * @return array
     */
    public function addQuoteDataProvider()
    {
        return [
            [
                [
                    ['SKUA', 'First product',  123.45, 1],
                    ['SKUB', 'Second product', 6780,   2]
                ],
                123456.78
            ],
            [
                [
                    ['',    '',    '123.45', '0'],
                    [null,  true,  0,        false],
                    [false, 0,     -123,     -2]
                ],
                null
            ]
        ];
    }

    /**
     * Test for \Henhed\Piwik\Helper\Tracker::addQuote
     *
     * Also covers `addQuoteItem' and `addQuoteTotal'
     *
     * @param array $items
     * @param float $total
     * @dataProvider addQuoteDataProvider
     */
    public function testAddQuote($items, $total)
    {
        // Build expected tracker result from $items and $total
        $expectedResult = [];
        foreach ($items as $item) {
            list($sku, $name, $price, $qty) = $item;
            $expectedResult[] = [
                'addEcommerceItem',
                $sku,
                $name,
                false,
                (float) $price,
                (float) $qty
            ];
        }
        $expectedResult[] = ['trackEcommerceCartUpdate', (float) $total];

        // Test `addQuote' with mock quote created from $items and $total
        $this->_helper->addQuote(
            $this->_getQuoteMock($items, $total),
            $this->_tracker
        );
        $this->assertSame($expectedResult, $this->_tracker->toArray());
    }

    /**
     * Order collection data provider
     *
     * @return array
     */
    public function addOrdersDataProvider()
    {
        return [
            [
                // Sample orders data
                [
                    [
                        '100001', 123.45, 101, 10, 15, -5,
                        [
                            ['sku1', 'Name 1', 50, 2, null],
                            ['sku2', 'Name 2', 50, 3, null]
                        ]
                    ],
                    [
                        '100002', '234.56', '201', '15', '20', '-50',
                        [
                            ['sku2', 'Name 2 (2)', '60', '1', null],
                            ['sku3', 'Name 3',     '70', '2', null],
                            ['sku4', 'Name 4',      '0', '1',    1]
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

                    // `sku4' should be skipped as it's a child item

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
     * Test for \Henhed\Piwik\Helper\Tracker::addOrders
     *
     * @param array $ordersData
     * @param array $expectedResult
     * @return void
     * @dataProvider addOrdersDataProvider
     */
    public function testAddOrders($ordersData, $expectedResult)
    {
        $orders = [];
        foreach ($ordersData as $orderData) {
            list($incrementId, $grandTotal, $subTotal, $tax, $shipping,
                 $discount, $itemsData) = $orderData;
             $orders[] = $this->_getOrderMock(
                 $incrementId,
                 $grandTotal,
                 $subTotal,
                 $tax,
                 $shipping,
                 $discount,
                 $itemsData
             );
        }

        $this->assertSame(
            $this->_helper,
            $this->_helper->addOrders($orders, $this->_tracker)
        );
        $this->assertEquals($expectedResult, $this->_tracker->toArray());
    }

    /**
     * Create a mock quote object with given data
     *
     * @param array $items
     * @param float $total
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getQuoteMock($items, $total)
    {
        $quoteItems = [];
        foreach ($items as $itemData) {
            list($sku, $name, $price, $qty) = $itemData;
            $item = $this->createPartialMock(
                \Magento\Quote\Model\Quote\Item::class,
                ['getData']
            );
            $item
                ->expects($this->any())
                ->method('getData')
                ->willReturnMap([
                    ['sku',                 null, $sku],
                    ['name',                null, $name],
                    ['base_price_incl_tax', null, $price],
                    ['qty',                 null, $qty]
                ]);
            $quoteItems[] = $item;
        }

        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getAllVisibleItems', 'getData']
        );
        $quote
            ->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($quoteItems);
        $quote
            ->expects($this->any())
            ->method('getData')
            ->with('base_grand_total')
            ->willReturn($total);

        return $quote;
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
    protected function _getOrderMock(
        $incrementId,
        $grandTotal,
        $subTotal,
        $tax,
        $shipping,
        $discount,
        $itemsData
    ) {
        $items = [];
        foreach ($itemsData as $itemData) {
            list($sku, $name, $price, $qty, $parentId) = $itemData;
            $items[] = $this->createConfiguredMock(
                \Magento\Sales\Api\Data\OrderItemInterface::class,
                [
                    'getSku'              => $sku,
                    'getName'             => $name,
                    'getBasePriceInclTax' => $price,
                    'getQtyOrdered'       => $qty,
                    'getParentItemId'     => $parentId
                ]
            );
        }
        $orderMock = $this->createConfiguredMock(
            \Magento\Sales\Api\Data\OrderInterface::class,
            [
                'getIncrementId'         => $incrementId,
                'getBaseGrandTotal'      => $grandTotal,
                'getBaseSubtotalInclTax' => $subTotal,
                'getBaseTaxAmount'       => $tax,
                'getBaseShippingInclTax' => $shipping,
                'getBaseDiscountAmount'  => $discount,
                'getItems'               => $items
            ]
        );
        return $orderMock;
    }
}
