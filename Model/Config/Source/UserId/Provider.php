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

namespace Henhed\Piwik\Model\Config\Source\UserId;

/**
 * User ID provider config source model
 *
 */
class Provider implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * User ID provider pool
     *
     * @var \Henhed\Piwik\UserId\Provider\Pool $_pool
     */
    protected $_pool;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\UserId\Provider\Pool $pool
     */
    public function __construct(\Henhed\Piwik\UserId\Provider\Pool $pool)
    {
        $this->_pool = $pool;
    }

    /**
     * Return array of user ID providers as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => '', 'label' => __('No')]];
        foreach ($this->_pool->getAllProviders() as $code => $provider) {
            $options[] = [
                'value' => $code,
                'label' => sprintf('%s (%s)', __('Yes'), $provider->getTitle())
            ];
        }
        return $options;
    }
}
