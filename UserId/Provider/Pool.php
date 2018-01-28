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

namespace Henhed\Piwik\UserId\Provider;

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
