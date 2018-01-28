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
