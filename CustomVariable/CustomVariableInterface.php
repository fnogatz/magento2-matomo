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
 * Base interface for custom variables
 *
 */
interface CustomVariableInterface
{

    /**
     * Custom variable scope constants
     */
    const SCOPE_PAGE = 'page';
    const SCOPE_VISIT = 'visit';

    /**
     * Custom variable value format hints
     */
    const VALUE_HINT_ID = 'id';
    const VALUE_HINT_CODE = 'code';
    const VALUE_HINT_LABEL = 'label';

    /**
     * Retrieve custom variable name
     *
     * @return string
     */
    public function getName();

    /**
     * Get this variables value for given context
     *
     * @param Context $context
     * @param string $hint
     * @return string|false
     */
    public function getValue(Context $context, $hint = self::VALUE_HINT_ID);

    /**
     * Returns the scope for which this variable is restricted or boolean FALSE
     * if there is no restriction.
     *
     * @return string|false
     */
    public function getScopeRestriction();
}
