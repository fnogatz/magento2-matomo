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

namespace Henhed\Piwik\Test\Unit\CustomVariable;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Henhed\Piwik\CustomVariable\CustomVariableInterface;

/**
 * Test for \Henhed\Piwik\CustomVariable\Pool
 *
 */
class PoolTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Object Manager instance
     *
     * @var ObjectManager $_objectManager
     */
    protected $_objectManager;

    /**
     * Custom variable pool instance
     *
     * @var \Henhed\Piwik\CustomVariable\Pool $_pool
     */
    protected $_pool;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_pool = $this->_createPool();
    }

    /**
     * Create variable pool instance with given variables passed to constructor
     *
     * @param array $variables
     * @return \Henhed\Piwik\CustomVariable\Pool
     */
    protected function _createPool(array $variables = [])
    {
        return $this->_objectManager->getObject(
            \Henhed\Piwik\CustomVariable\Pool::class,
            ['variables' => $variables]
        );
    }

    /**
     * Create custom variable mock object with given scope restriction
     *
     * @param string|false $scope
     * @return \Henhed\Piwik\CustomVariable\CustomVariableInterface
     */
    protected function _createVariableMock($scope = false)
    {
        $variable = $this->getMock(CustomVariableInterface::class, []);
        $variable->method('getScopeRestriction')->willReturn($scope);
        return $variable;
    }

    /**
     * Test \Henhed\Piwik\CustomVariable\Pool::addVariable
     *
     * @return void
     */
    public function testAddVariable()
    {
        $noScope = false;
        $pageScope = CustomVariableInterface::SCOPE_PAGE;
        $visitScope = CustomVariableInterface::SCOPE_VISIT;
        $pageVariableCount = 0;
        $visitVariableCount = 0;
        $variables = [
            'n1' => $this->_createVariableMock($noScope),
            'p1' => $this->_createVariableMock($pageScope),
            'v1' => $this->_createVariableMock($visitScope),
            'p2' => $this->_createVariableMock($pageScope),
            'n2' => $this->_createVariableMock($noScope)
        ];

        foreach ($variables as $code => $variable) {
            $this->_pool->addVariable($code, $variable);
            $restriction = $variable->getScopeRestriction();
            if ($restriction != $visitScope) {
                ++$pageVariableCount;
            }
            if ($restriction != $pageScope) {
                ++$visitVariableCount;
            }
        }

        foreach ($variables as $code => $variable) {
            $this->assertSame(
                $variable,
                $this->_pool->getVariableByCode($code)
            );
        }

        $this->assertCount(count($variables), $this->_pool->getAllVariables());
        $this->assertCount(
            $pageVariableCount,
            $this->_pool->getVariablesByScope($pageScope)
        );
        $this->assertCount(
            $visitVariableCount,
            $this->_pool->getVariablesByScope($visitScope)
        );
    }

    /**
     * Test \Henhed\Piwik\CustomVariable\Pool::addVariable given a variable
     * with a code that already exists.
     *
     * @return void
     */
    public function testAddVariableWithExistingCode()
    {
        $code = '__EXISTING_CODE__';
        $this->_pool->addVariable($code, $this->_createVariableMock());
        $this->setExpectedException('UnexpectedValueException', $code);
        $this->_pool->addVariable($code, $this->_createVariableMock());
    }

    /**
     * Test \Henhed\Piwik\CustomVariable\Pool::addVariable given a variable
     * with an invalid scope restriction.
     *
     * @return void
     */
    public function testAddVariableWithInvalidScopeRestriction()
    {
        $scope = '__INVALID_SCOPE__';
        $variable = $this->_createVariableMock($scope);
        $this->setExpectedException('LogicException', $scope);
        $this->_pool->addVariable('test', $variable);
    }

    /**
     * Test \Henhed\Piwik\CustomVariable\Pool::__construct
     *
     * @return void
     */
    public function testConstructor()
    {
        $variables = [
            'v1' => $this->_createVariableMock(),
            'v2' => $this->_createVariableMock()
        ];
        $pool = $this->_createPool($variables);
        $this->assertEquals($variables, $pool->getAllVariables());

        $this->setExpectedException('LogicException', 'stdClass');
        $variables['v3'] = new \stdClass;
        $this->_createPool($variables);
    }
}
