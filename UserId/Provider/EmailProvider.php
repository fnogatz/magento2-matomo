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

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Customer email provider
 *
 */
class EmailProvider implements ProviderInterface
{

    /**
     * Customer repository
     *
     * @var CustomerRepositoryInterface $_customerRepository
     */
    protected $_customerRepository;

    /**
     * Constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->_customerRepository = $customerRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserId($customerId)
    {
        try {
            return $this->_customerRepository->getById($customerId)->getEmail();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return __('Customer E-mail');
    }
}
