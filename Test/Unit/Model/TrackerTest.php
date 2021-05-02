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

namespace Chessio\Matomo\Test\Unit\Model;

use \Chessio\Matomo\Model\Tracker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Chessio\Matomo\Model\Tracker
 *
 */
class TrackerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Tracker instance
     *
     * @var \Chessio\Matomo\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Action factory mock
     *
     * @var \PHPUnit_Framework_MockObject_MockObject $_actionFactory
     */
    protected $_actionFactory;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $className = \Chessio\Matomo\Model\Tracker::class;
        $objectManager = new ObjectManager($this);
        $arguments = $objectManager->getConstructArguments($className, [
            'actionFactory' => $this->createPartialMock(
                \Chessio\Matomo\Model\Tracker\ActionFactory::class,
                ['create']
            )
        ]);
        $this->_tracker = $objectManager->getObject($className, $arguments);
        $this->_actionFactory = $arguments['actionFactory'];
    }

    /**
     * Test tracker action push
     *
     * Covers Tracker::push and Tracker::toArray
     *
     * @param string $name
     * @param array $args
     * @dataProvider trackerActionDataProvider
     */
    public function testPush($name, $args)
    {
        $this->_tracker->push(new Tracker\Action($name, $args));
        $this->assertEquals(
            [array_merge([$name], $args)],
            $this->_tracker->toArray()
        );
    }

    /**
     * Test magic tracker action push
     *
     * Covers Tracker::__call and Tracker::toArray
     *
     * @param string $name
     * @param array $args
     * @dataProvider trackerActionDataProvider
     */
    public function testMagicPush($name, $args)
    {
        $this->_actionFactory
            ->expects($this->once())
            ->method('create')
            ->with([
                'name' => $name,
                'args' => $args
            ])
            ->will($this->returnValue(new Tracker\Action($name, $args)));

        // @codingStandardsIgnoreStart
        call_user_func_array([$this->_tracker, $name], $args);
        // @codingStandardsIgnoreEnd

        $this->assertEquals(
            [array_merge([$name], $args)],
            $this->_tracker->toArray()
        );
    }

    /**
     * Tracker action data provider
     *
     * @return array
     */
    public function trackerActionDataProvider()
    {
        return [
            ['trackEvent',      ['category', 'action', 'name', 1]],
            ['trackPageView',   ['customTitle']],
            ['trackSiteSearch', ['keyword', 'category', 0]],
            ['trackGoal',       [1, 1.1]],
            ['trackLink',       ['url', 'linkType']],
            ['disableCookies',  []]
        ];
    }
}
