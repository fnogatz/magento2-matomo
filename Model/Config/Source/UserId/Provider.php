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

namespace Chessio\Matomo\Model\Config\Source\UserId;

/**
 * User ID provider config source model
 *
 */
class Provider implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * User ID provider pool
     *
     * @var \Chessio\Matomo\UserId\Provider\Pool $_pool
     */
    protected $_pool;

    /**
     * Constructor
     *
     * @param \Chessio\Matomo\UserId\Provider\Pool $pool
     */
    public function __construct(\Chessio\Matomo\UserId\Provider\Pool $pool)
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
