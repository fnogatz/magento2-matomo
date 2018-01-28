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
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Henhed\Piwik\Helper\Data
 *
 */
class DataTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Piwik data helper (test subject) instance
     *
     * @var \Henhed\Piwik\Helper\Data $_helper
     */
    protected $_helper;

    /**
     * Scope config mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * Request mock object
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $className = \Henhed\Piwik\Helper\Data::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className);
        $this->_helper = $objectManager->getObject($className, $arguments);
        $context = $arguments['context'];
        /** @var \Magento\Framework\App\Helper\Context $context */
        $this->_scopeConfigMock = $context->getScopeConfig();
        $this->_requestMock = $context->getRequest();
    }

    /**
     * Prepare scope config mock with given values
     *
     * @param string $enabled
     * @param string $hostname
     * @param string $siteId
     * @param string $linkEnabled
     * @param string $linkDelay
     * @param string $phpScriptPath
     * @param string $jsScriptPath
     * @param string $cdnHostname
     * @param string $scope
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     */
    protected function _prepareScopeConfigMock(
        $enabled = null,
        $hostname = null,
        $siteId = null,
        $linkEnabled = null,
        $linkDelay = null,
        $phpScriptPath = null,
        $jsScriptPath = null,
        $cdnHostname = null,
        $scope = ScopeInterface::SCOPE_STORE,
        $store = null
    ) {
        $this->_scopeConfigMock
            ->expects($this->any())
            ->method('isSetFlag')
            ->will($this->returnValueMap([
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_ENABLED,
                    $scope, $store, $enabled
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_LINK_ENABLED,
                    $scope, $store, $linkEnabled
                ]
            ]));

        $this->_scopeConfigMock
            ->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_HOSTNAME,
                    $scope, $store, $hostname
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_SITE_ID,
                    $scope, $store, $siteId
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_LINK_DELAY,
                    $scope, $store, $linkDelay
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_PHP_SCRIPT_PATH,
                    $scope, $store, $phpScriptPath
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_JS_SCRIPT_PATH,
                    $scope, $store, $jsScriptPath
                ],
                [
                    \Henhed\Piwik\Helper\Data::XML_PATH_CDN_HOSTNAME,
                    $scope, $store, $cdnHostname
                ]
            ]));
    }

    /**
     * Data provider for `testIsTrackingEnabled'
     *
     * @return array
     */
    public function isTrackingEnabledDataProvider()
    {
        return [
            [true,  'piwik.example.org', 1, true],
            [true,  '',                  1, false],
            [true,  ' ',                 1, false],
            [true,  'example.org/piwik', 0, false],
            [false, 'piwik.org',         1, false]
        ];
    }

    /**
     * Test \Henhed\Piwik\Helper\Data::isTrackingEnabled
     *
     * Also covers `getHostname' and `getSiteId'
     *
     * @param bool $enabled
     * @param string $hostname
     * @param int $siteId
     * @param bool $returnValue
     * @return void
     * @dataProvider isTrackingEnabledDataProvider
     */
    public function testIsTrackingEnabled(
        $enabled,
        $hostname,
        $siteId,
        $returnValue
    ) {
        $this->_prepareScopeConfigMock($enabled, $hostname, $siteId);
        $this->assertEquals($returnValue, $this->_helper->isTrackingEnabled());
    }

    /**
     * Data provider for `testGetPhpScriptUrl'
     *
     * @return array
     */
    public function phpScriptUrlDataProvider()
    {
        return [
            [
                'piwik.org',
                false, // should prepend `http://'
                null, // should fall back on `piwik.php'
                // Expected result
                'http://piwik.org/piwik.php'
            ],
            [
                'example.com/piwik',
                true, // should prepend `https://'
                'tracker.php', // should override `piwik.php'
                // Expected result
                'https://example.com/piwik/tracker.php'
            ],
            [
                ' https://example.com/ ', // should be trimmed
                false, // should replace `https://' with `http://'
                ' /piwik/tracker.php ', // should be trimmed
                // Expected result
                'http://example.com/piwik/tracker.php'
            ]
        ];
    }

    /**
     * Test \Henhed\Piwik\Helper\Data::getPhpScriptUrl
     *
     * @param string $hostname
     * @param bool $isSecure
     * @param string $phpScriptPath
     * @param string $returnValue
     * @dataProvider phpScriptUrlDataProvider
     */
    public function testGetPhpScriptUrl(
        $hostname,
        $isSecure,
        $phpScriptPath,
        $returnValue
    ) {
        $this->_prepareScopeConfigMock(
            null,
            $hostname,
            null,
            null,
            null,
            $phpScriptPath
        );

        // Test explicit `isSecure'
        $this->assertEquals(
            $returnValue,
            $this->_helper->getPhpScriptUrl(null, $isSecure)
        );

        // Test implicit `isSecure'
        $this->_requestMock
            ->expects($this->once())
            ->method('isSecure')
            ->will($this->returnValue($isSecure));

        $this->assertEquals($returnValue, $this->_helper->getPhpScriptUrl());
    }

    /**
     * Data provider for `testGetJsScriptUrl'
     *
     * @return array
     */
    public function jsScriptUrlDataProvider()
    {
        return [
            [
                'piwik.org',
                false, // should prepend `http://'
                null, // should fall back on `piwik.js'
                null, // should fall back on regular hostname
                // Expected result
                'http://piwik.org/piwik.js'
            ],
            [
                ' piwik.org/path/ ', // should be trimmed
                true, // should prepend `https://'
                'example.js', // should override `piwik.js'
                null, // should fall back on hostname
                // Expected result
                'https://piwik.org/path/example.js'
            ],
            [
                'piwik.org', // should be ignored
                true, // should replace `http://' with `https://''
                ' /to/tracker.js ', // should be trimmed
                'http://cdn.example.com/path/', // should override hostname
                // Expected result
                'https://cdn.example.com/path/to/tracker.js'
            ]
        ];
    }

    /**
     * Test \Henhed\Piwik\Helper\Data::getJsScriptUrl
     *
     * @param string $hostname
     * @param bool $isSecure
     * @param string $jsScriptPath
     * @param string $cdnHostname
     * @param string $returnValue
     * @dataProvider jsScriptUrlDataProvider
     */
    public function testGetJsScriptUrl(
        $hostname,
        $isSecure,
        $jsScriptPath,
        $cdnHostname,
        $returnValue
    ) {
        $this->_prepareScopeConfigMock(
            null,
            $hostname,
            null,
            null,
            null,
            null,
            $jsScriptPath,
            $cdnHostname
        );

        // Test explicit `isSecure'
        $this->assertEquals(
            $returnValue,
            $this->_helper->getJsScriptUrl(null, $isSecure)
        );

        // Test implicit `isSecure'
        $this->_requestMock
            ->expects($this->once())
            ->method('isSecure')
            ->will($this->returnValue($isSecure));

        $this->assertEquals($returnValue, $this->_helper->getJsScriptUrl());
    }

    /**
     * Data provider for `testIsLinkTrackingEnabled'
     *
     * @return array
     */
    public function isLinkTrackingEnabledDataProvider()
    {
        return [
            [true,  true,  'piwik.example.org', 1, true],
            [false, true,  'piwik.example.org', 2, false],
            [true,  true,  '',                  1, false],
            [true,  true,  ' ',                 1, false],
            [false, true,  'example.org/piwik', 0, false],
            [true,  false, 'piwik.org',         1, false]
        ];
    }

    /**
     * Test \Henhed\Piwik\Helper\Data::isLinkTrackingEnabled
     *
     * Also covers `isTrackingEnabled'
     *
     * @param bool $linkEnabled
     * @param bool $enabled
     * @param string $hostname
     * @param int $siteId
     * @param bool $returnValue
     * @dataProvider isLinkTrackingEnabledDataProvider
     */
    public function testIsLinkTrackingEnabled(
        $linkEnabled,
        $enabled,
        $hostname,
        $siteId,
        $returnValue
    ) {
        $this->_prepareScopeConfigMock(
            $enabled,
            $hostname,
            $siteId,
            $linkEnabled
        );

        $this->assertEquals(
            $returnValue,
            $this->_helper->isLinkTrackingEnabled()
        );
    }
}
