<?php
/**
 * Copyright 2016 Henrik Hedelund
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

namespace Henhed\Piwik\Test\Unit\CustomerData\Checkout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Henhed\Piwik\CustomerData\Checkout\CartPlugin
 *
 */
class CartPluginTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Customer data checkout cart plugin (test subject) instance
     *
     * @var \Henhed\Piwik\CustomerData\Checkout\CartPlugin $_cartPlugin
     */
    protected $_cartPlugin;

    /**
     * Cart customer data mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_cartMock
     */
    protected $_cartMock;

    /**
     * Quote model mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_quoteMock
     */
    protected $_quoteMock;

    /**
     * Piwik data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Tracker model mock object
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
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $className = 'Henhed\Piwik\CustomerData\Checkout\CartPlugin';
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className, [
            'trackerFactory' => $this->getMock(
                'Henhed\Piwik\Model\TrackerFactory',
                ['create'], [], '', false
            )
        ]);
        $this->_cartPlugin = $objectManager->getObject($className, $arguments);

        $this->_quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote', ['getId'], [], '', false
        );
        $arguments['checkoutSession']
            ->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        $this->_dataHelperMock = $arguments['dataHelper'];
        $this->_trackerMock = $this->getMock(
            'Henhed\Piwik\Model\Tracker', [], [], '', false
        );
        $arguments['trackerFactory']
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->_trackerMock);

        $this->_trackerHelperMock = $arguments['trackerHelper'];

        $this->_cartMock = $this->getMock(
            'Magento\Checkout\CustomerData\Cart', [], [], '', false
        );
    }

    /**
     * Test \Henhed\Piwik\CustomerData\Checkout\CartPlugin::afterGetSectionData
     * with existing quote.
     *
     * @return void
     */
    public function testafterGetSectionData()
    {
        $expectedResult = ['piwikActions' => ['someKey' => 'someValue']];

        // Enable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        // Give ID to quote mock
        $this->_quoteMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        // Make sure tracker helpers' `addQuote' is called exactly once
        $this->_trackerHelperMock
            ->expects($this->once())
            ->method('addQuote')
            ->with($this->_quoteMock, $this->_trackerMock)
            ->willReturn($this->_trackerHelperMock);

        // Make tracker mock return expected data
        $this->_trackerMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn($expectedResult['piwikActions']);

        // Assert that result of plugin equals expected result
        $this->assertEquals(
            $expectedResult,
            $this->_cartPlugin->afterGetSectionData($this->_cartMock, [])
        );
    }

    /**
     * Test \Henhed\Piwik\CustomerData\Checkout\CartPlugin::afterGetSectionData
     * with empty quote.
     *
     * @return void
     */
    public function testafterGetSectionDataWithEmptyQuote()
    {
        // Enable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(true);

        // Make sure tracker methods are never called
        $this->_trackerHelperMock
            ->expects($this->never())
            ->method('addQuote');
        $this->_trackerMock
            ->expects($this->never())
            ->method('toArray');

        // Assert that result of plugin is same as input
        $result = ['someKey' => 'someValue'];
        $this->assertEquals(
            $result,
            $this->_cartPlugin->afterGetSectionData($this->_cartMock, $result)
        );
    }

    /**
     * Test \Henhed\Piwik\CustomerData\Checkout\CartPlugin::afterGetSectionData
     * with tracking disabled.
     *
     * @return void
     */
    public function testafterGetSectionDataWithTrackingDisabled()
    {
        // Disable tracking
        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn(false);

        // Give ID to quote mock
        $this->_quoteMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        // Make sure tracker methods are never called
        $this->_trackerHelperMock
            ->expects($this->never())
            ->method('addQuote');
        $this->_trackerMock
            ->expects($this->never())
            ->method('toArray');

        // Assert that result of plugin is same as input
        $result = ['someKey' => 'someValue'];
        $this->assertEquals(
            $result,
            $this->_cartPlugin->afterGetSectionData($this->_cartMock, $result)
        );
    }
}
