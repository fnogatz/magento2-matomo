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

namespace Henhed\Piwik\Model\Config\Source;

/**
 * Custom variable config source
 *
 */
class CustomVariable implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Custom variables
     *
     * @var array $_customVariables
     */
    protected $_customVariables;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\CustomVariable\Pool $pool
     * @param string|false $scope
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Henhed\Piwik\CustomVariable\Pool $pool,
        $scope = false
    ) {
        if ($scope) {
            $this->_customVariables = $pool->getVariablesByScope($scope);
        } else {
            $this->_customVariables = $pool->getAllVariables();
        }

        uasort($this->_customVariables, function ($lhs, $rhs) {
            return strcmp($lhs->getName(), $rhs->getName());
        });
    }

    /**
     * Return array of custom visit variables as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->_customVariables as $code => $variable) {
            $options[] = [
                'value' => $code,
                'label' => $variable->getName()
            ];
        }
        return $options;
    }
}
