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

namespace Henhed\Piwik\Model\Config\Source\CustomVariable;

use Henhed\Piwik\CustomVariable\CustomVariableInterface;

/**
 * Custom variable value hint config source
 *
 */
class ValueHint implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of custom variable value hints as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => CustomVariableInterface::VALUE_HINT_ID,
                'label' => __('ID')
            ],
            [
                'value' => CustomVariableInterface::VALUE_HINT_CODE,
                'label' => __('Code')
            ],
            [
                'value' => CustomVariableInterface::VALUE_HINT_LABEL,
                'label' => __('Label')
            ]
        ];
    }
}
