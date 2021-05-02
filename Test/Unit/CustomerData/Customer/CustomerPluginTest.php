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

namespace Chessio\Matomo\Test\Unit\CustomerData\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Chessio\Matomo\CustomerData\Customer\CustomerPlugin
 *
 */
class CustomerPluginTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Customer data plugin (test subject) instance
     *
     * @var \Chessio\Matomo\CustomerData\Customer\CustomerPlugin $_customerPlugin
     */
    protected $_customerPlugin;

    /**
     * Current customer helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_currentCustomerMock
     */
    protected $_currentCustomerMock;

    /**
     * Matomo data helper mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_dataHelperMock
     */
    protected $_dataHelperMock;

    /**
     * Matomo user ID provider pool mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_uidProviderPoolMock
     */
    protected $_uidProviderPoolMock;

    /**
     * Matomo user ID provider mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_uidProviderMock
     */
    protected $_uidProviderMock;

    /**
     * Customer data mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_customerDataMock
     */
    protected $_customerDataMock;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp(): void
    {
        $className = \Chessio\Matomo\CustomerData\Customer\CustomerPlugin::class;
        $objectManager = new ObjectManager($this);
        $args = $objectManager->getConstructArguments($className);
        $this->_customerPlugin = $objectManager->getObject($className, $args);
        $this->_currentCustomerMock = $args['currentCustomer'];
        $this->_dataHelperMock = $args['dataHelper'];
        $this->_uidProviderPoolMock = $args['uidProviderPool'];
        $this->_uidProviderMock = $this->createPartialMock(
            \Chessio\Matomo\UserId\Provider\ProviderInterface::class,
            ['getUserId', 'getTitle']
        );
        $this->_customerDataMock = $this->createMock(
            \Magento\Customer\CustomerData\Customer::class
        );
    }

    /**
     * Data provider for `testafterGetSectionData'
     *
     * @return array
     */
    public function testafterGetSectionDataDataProvider()
    {
        return [
            [false, 1,    'p',  'UID1'],
            [true,  null, 'p',  'UID2'],
            [true,  3,    'p',  ''],
            [true,  4,    null, 'UID4'],
            [true,  5,    'p',  'UID5']
        ];
    }

    /**
     * Test `afterGetSectionData'
     *
     * @param bool $enabled
     * @param int $customerId
     * @param string|null $provider
     * @param string $userId
     * @return void
     * @dataProvider testafterGetSectionDataDataProvider
     */
    public function testafterGetSectionData(
        $enabled,
        $customerId,
        $provider,
        $userId
    ) {
        $expectedResult = [];
        if ($enabled && $customerId && $provider && $userId) {
            $expectedResult['matomoUserId'] = $userId;
        }

        $this->_dataHelperMock
            ->expects($this->once())
            ->method('isTrackingEnabled')
            ->willReturn($enabled);

        $this->_dataHelperMock
            ->expects($enabled ? $this->once() : $this->never())
            ->method('getUserIdProviderCode')
            ->willReturn($provider);

        $this->_currentCustomerMock
            ->expects($enabled ? $this->once() : $this->never())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->_uidProviderPoolMock
            ->expects(
                ($enabled && $provider)
                    ? $this->once()
                    : $this->never()
            )
            ->method('getProviderByCode')
            ->with($provider)
            ->willReturn($this->_uidProviderMock);

        $this->_uidProviderMock
            ->expects(
                ($enabled && $customerId && $provider)
                    ? $this->once()
                    : $this->never()
            )
            ->method('getUserId')
            ->with($customerId)
            ->willReturn($userId);

        // Assert that result of plugin equals expected result
        $this->assertEquals(
            $expectedResult,
            $this->_customerPlugin->afterGetSectionData(
                $this->_customerDataMock,
                []
            )
        );
    }
}
