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

namespace Chessio\Matomo\CustomerData\Customer;

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
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * User ID provider pool
     *
     * @var \Chessio\Matomo\UserId\Provider\Pool $_uidProviderPool
     */
    protected $_uidProviderPool;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param \Chessio\Matomo\UserId\Provider\Pool $uidProviderPool
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Chessio\Matomo\Helper\Data $dataHelper,
        \Chessio\Matomo\UserId\Provider\Pool $uidProviderPool
    ) {
        $this->_currentCustomer = $currentCustomer;
        $this->_dataHelper = $dataHelper;
        $this->_uidProviderPool = $uidProviderPool;
    }

    /**
     * Get configured Matomo User ID provider or NULL
     *
     * @return \Chessio\Matomo\UserId\Provider\ProviderInterface|null
     */
    protected function _getUserIdProvider()
    {
        $code = $this->_dataHelper->getUserIdProviderCode();
        return $code ? $this->_uidProviderPool->getProviderByCode($code) : null;
    }

    /**
     * Get Matomo User ID for current customer
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
                $result['matomoUserId'] = $userId;
            }
        }
        return $result;
    }
}
