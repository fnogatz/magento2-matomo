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

namespace Henhed\Piwik\CustomerData\Customer;

/**
 * Plugin for \Magento\Customer\CustomerData\Customer
 *
 */
class CustomerPlugin
{

    /**
     * Current customer helper
     *
     * @var \Magento\Customer\Helper\Session\CurrentCustomer $_currentCustomer
     */
    protected $_currentCustomer;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * User ID provider pool
     *
     * @var \Henhed\Piwik\UserId\Provider\Pool $_uidProviderPool
     */
    protected $_uidProviderPool;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Henhed\Piwik\UserId\Provider\Pool $uidProviderPool
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Henhed\Piwik\UserId\Provider\Pool $uidProviderPool
    ) {
        $this->_currentCustomer = $currentCustomer;
        $this->_dataHelper = $dataHelper;
        $this->_uidProviderPool = $uidProviderPool;
    }

    /**
     * Get configured Piwik User ID provider or NULL
     *
     * @return \Henhed\Piwik\UserId\Provider\ProviderInterface|null
     */
    protected function _getUserIdProvider()
    {
        $code = $this->_dataHelper->getUserIdProviderCode();
        return $code ? $this->_uidProviderPool->getProviderByCode($code) : null;
    }

    /**
     * Get Piwik User ID for current customer
     *
     * @return string
     */
    protected function _getUserId()
    {
        $provider = $this->_getUserIdProvider();
        $customerId = $this->_currentCustomer->getCustomerId();
        return ($provider && $customerId)
            ? (string) $provider->getUserId($customerId)
            : '';
    }

    /**
     * Add visitor related tracker information to customer section data.
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(
        \Magento\Customer\CustomerData\Customer $subject,
        $result
    ) {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $userId = $this->_getUserId();
            if ($userId !== '') {
                $result['piwikUserId'] = $userId;
            }
        }
        return $result;
    }
}
