<?php
/**
 * Copyright 2015 Henrik Hedelund
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

namespace Henhed\Piwik\Model\Tracker\Action;

use Henhed\Piwik\Model\Tracker;

/**
 * Piwik tracker action queue
 *
 */
class Queue implements \IteratorAggregate
{

    /**
     * Action items
     *
     * @var \Henhed\Piwik\Model\Tracker\Action[] $_actions
     */
    protected $_actions = [];

    /**
     * Push a tracker action to this queue
     *
     * @param \Henhed\Piwik\Model\Tracker\Action $action
     * @return \Henhed\Piwik\Model\Tracker\Action\Queue
     */
    public function push(Tracker\Action $action)
    {
        $this->_actions[] = $action;
        return $this;
    }

    /**
     * Implementation of \IteratorAggregate
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_actions);
    }

    /**
     * Magic action push function
     *
     * @param string $name
     * @param array $arguments
     * @return \Henhed\Piwik\Model\Tracker\Action\Queue
     */
    public function __call($name, $arguments)
    {
        return $this->push(new Tracker\Action($name, $arguments));
    }
}
