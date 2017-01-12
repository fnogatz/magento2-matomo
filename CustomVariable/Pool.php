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

namespace Henhed\Piwik\CustomVariable;

/**
 * Custom Variable pool
 *
 */
class Pool
{

    /**
     * All custom variables
     *
     * @var CustomVariableInterface[] $_variables
     */
    protected $_allVariables = [];

    /**
     * All custom variables grouped by scope
     *
     * @var CustomVariableInterface[][] $_variablesByScope
     */
    protected $_variablesByScope = [
        CustomVariableInterface::SCOPE_PAGE => [],
        CustomVariableInterface::SCOPE_VISIT => []
    ];

    /**
     * Constructor
     *
     * @param CustomVariableInterface[] $variables
     * @throws \LogicException
     */
    public function __construct(array $variables = [])
    {
        foreach ($variables as $code => $variable) {
            if ($variable instanceof CustomVariableInterface) {
                $this->addVariable($code, $variable);
            } else {
                throw new \LogicException(sprintf(
                    '%s must implement %s',
                    get_class($variable), CustomVariableInterface::class
                ));
            }
        }
    }

    /**
     * Add a custom variable to this pool
     *
     * @param string $code
     * @param CustomVariableInterface $variable
     * @return Pool
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    public function addVariable($code, CustomVariableInterface $variable)
    {
        if (isset($this->_allVariables[$code])) {
            throw new \UnexpectedValueException(sprintf(
                'Variable with code %s already exists',
                $code
            ));
        }

        $scope = $variable->getScopeRestriction();
        if ($scope !== false) {
            if (!isset($this->_variablesByScope[$scope])) {
                throw new \LogicException(sprintf(
                    '%s is not a valid custom variable scope restriction',
                    $scope
                ));
            }
            $this->_variablesByScope[$scope][$code] = $variable;
        } else {
            foreach (array_keys($this->_variablesByScope) as $scope) {
                $this->_variablesByScope[$scope][$code] = $variable;
            }
        }

        $this->_allVariables[$code] = $variable;

        return $this;
    }

    /**
     * Retrieve all available variables
     *
     * @return CustomVariableInterface[]
     */
    public function getAllVariables()
    {
        return $this->_allVariables;
    }

    /**
     * Retrieve variable by code
     *
     * @param string $code
     * @return CustomVariableInterface|null
     */
    public function getVariableByCode($code)
    {
        return isset($this->_allVariables[$code])
            ? $this->_allVariables[$code]
            : null;
    }

    /**
     * Retrieve all available variables for given scope
     *
     * @return CustomVariableInterface[]
     * @throws \InvalidArgumentException
     */
    public function getVariablesByScope($scope)
    {
        if (!isset($this->_variablesByScope[$scope])) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a valid custom variable scope',
                $scope
            ));
        }
        return $this->_variablesByScope[$scope];
    }

    /**
     * Retrieve all available page variables
     *
     * @return CustomVariableInterface[]
     */
    public function getPageVariables()
    {
        return $this->getVariablesByScope(CustomVariableInterface::SCOPE_PAGE);
    }

    /**
     * Retrieve all available visit variables
     *
     * @return CustomVariableInterface[]
     */
    public function getVisitVariables()
    {
        return $this->getVariablesByScope(CustomVariableInterface::SCOPE_VISIT);
    }
}
