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
 * Custom variable context
 *
 */
class Context
{

    /**
     * Custom variable scope of this context
     *
     * @var string|false $_scope
     */
    protected $_scope;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    protected $_storeManager;

    /**
     * Locale resolver
     *
     * @var \Magento\Framework\Locale\ResolverInterface $_localeResolver
     */
    protected $_localeResolver;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session $_customerSession
     */
    protected $_customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Customer\Model\Session $customerSession
     * @param string|false $scope
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\Session $customerSession,
        $scope = false
    ) {
        $this->_storeManager = $storeManager;
        $this->_localeResolver = $localeResolver;
        $this->_customerSession = $customerSession;
        $this->_scope = $scope;
    }

    /**
     * Get scope of this context or boolean FALSE if no scope is defined
     *
     * @return string|false
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Retrieve current store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Retrieve current currency or NULL on failure
     *
     * @return \Magento\Directory\Model\Currency|null
     */
    public function getCurrency()
    {
        $store = $this->getStore();
        if ($store instanceof \Magento\Store\Model\Store) {
            return $store->getCurrentCurrency();
        }
        return null;
    }

    /**
     * Retrieve current locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_localeResolver->getLocale();
    }

    /**
     * Retrieve current customer group ID
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_customerSession->getCustomerGroupId();
    }
}
