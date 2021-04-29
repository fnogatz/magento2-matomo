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

namespace Chessio\Matomo\UserId\Provider;

/**
 * User ID provider pool
 *
 */
class Pool
{

    /**
     * User ID providers
     *
     * @var ProviderInterface[] $_providers
     */
    protected $_providers = [];

    /**
     * Constructor
     *
     * @param ProviderInterface[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->_providers = $providers;
    }

    /**
     * Get User ID provider by code
     *
     * @param string $code
     * @return ProviderInterface|null
     */
    public function getProviderByCode($code)
    {
        if (isset($this->_providers[$code])
            && ($this->_providers[$code] instanceof ProviderInterface)
        ) {
            return $this->_providers[$code];
        }
        return null;
    }

    /**
     * Get all User ID providers added to this pool
     *
     * @return ProviderInterface[]
     */
    public function getAllProviders()
    {
        return array_filter($this->_providers, function ($provider) {
            return $provider instanceof ProviderInterface;
        });
    }
}
