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

namespace Henhed\Piwik\Model\Tracker;

/**
 * Piwik tracker action
 *
 */
class Action
{

    /**
     * Action name
     *
     * @var string $_name
     */
    protected $_name;

    /**
     * Action arguments
     *
     * @var array $_args
     */
    protected $_args;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $args
     */
    public function __construct($name, array $args = [])
    {
        $this->_name = $name;
        $this->_args = $args;
    }

    /**
     * Get action name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get action arguments
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->_args;
    }

    /**
     * Get an array representation of this action
     *
     * @return array
     */
    public function toArray()
    {
        $array = $this->getArgs();
        array_unshift($array, $this->getName());
        return $array;
    }
}
