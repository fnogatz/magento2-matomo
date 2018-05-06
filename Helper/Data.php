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

namespace Henhed\Piwik\Helper;

use Magento\Store\Model\Store;

/**
 * Piwik data helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * System config XML paths
     */
    const XML_PATH_ENABLED = 'piwik/tracking/enabled';
    const XML_PATH_HOSTNAME = 'piwik/tracking/hostname';
    const XML_PATH_CDN_HOSTNAME = 'piwik/tracking/cdn_hostname';
    const XML_PATH_JS_SCRIPT_PATH = 'piwik/tracking/js_script_path';
    const XML_PATH_PHP_SCRIPT_PATH = 'piwik/tracking/php_script_path';
    const XML_PATH_SITE_ID = 'piwik/tracking/site_id';
    const XML_PATH_LINK_ENABLED = 'piwik/tracking/link_enabled';
    const XML_PATH_LINK_DELAY = 'piwik/tracking/link_delay';
    const XML_PATH_UID_PROVIDER = 'piwik/tracking/uid_provider';

    /**
     * Check if Piwik is enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isTrackingEnabled($store = null)
    {
        $hostname = $this->getHostname($store);
        $siteId = $this->getSiteId($store);
        return $hostname && $siteId && $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Piwik hostname
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getHostname($store = null)
    {
        return trim($this->scopeConfig->getValue(
            self::XML_PATH_HOSTNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    /**
     * Retrieve Piwik CDN hostname
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getCdnHostname($store = null)
    {
        return trim($this->scopeConfig->getValue(
            self::XML_PATH_CDN_HOSTNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    /**
     * Retrieve base URL for given hostname
     *
     * @param string $host
     * @param null|bool $secure
     * @return string
     */
    protected function _getBaseUrl($host, $secure = null)
    {
        if ($secure === null) {
            $secure = $this->_getRequest()->isSecure();
        }
        if (false !== ($scheme = strpos($host, '://'))) {
            $host = substr($host, $scheme + 3);
        }
        return ($secure ? 'https://' : 'http://') . rtrim($host, '/') . '/';
    }

    /**
     * Retrieve Piwik base URL
     *
     * @param null|string|bool|int|Store $store
     * @param null|bool $secure
     * @return string
     */
    public function getBaseUrl($store = null, $secure = null)
    {
        return $this->_getBaseUrl($this->getHostname($store), $secure);
    }

    /**
     * Retrieve Piwik CDN URL
     *
     * @param null|string|bool|int|Store $store
     * @param null|bool $secure
     * @return string
     */
    public function getCdnBaseUrl($store = null, $secure = null)
    {
        $host = $this->getCdnHostname($store);
        return ('' !== $host)
            ? $this->_getBaseUrl($host, $secure)
            : $this->getBaseUrl($store, $secure);
    }

    /**
     * Retrieve Piwik tracker JS script path
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getJsScriptPath($store = null)
    {
        return ltrim(trim($this->scopeConfig->getValue(
            self::XML_PATH_JS_SCRIPT_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )), '/') ?: 'piwik.js';
    }

    /**
     * Retrieve Piwik tracker JS script URL
     *
     * @param null|string|bool|int|Store $store
     * @param null|bool $secure
     * @return string
     */
    public function getJsScriptUrl($store = null, $secure = null)
    {
        return $this->getCdnBaseUrl($store, $secure)
             . $this->getJsScriptPath($store);
    }

    /**
     * Retrieve Piwik tracker PHP script path
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getPhpScriptPath($store = null)
    {
        return ltrim(trim($this->scopeConfig->getValue(
            self::XML_PATH_PHP_SCRIPT_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )), '/') ?: 'piwik.php';
    }

    /**
     * Retrieve Piwik tracker PHP script URL
     *
     * @param null|string|bool|int|Store $store
     * @param null|bool $secure
     * @return string
     */
    public function getPhpScriptUrl($store = null, $secure = null)
    {
        return $this->getBaseUrl($store, $secure)
             . $this->getPhpScriptPath($store);
    }

    /**
     * Retrieve Piwik site ID
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getSiteId($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_SITE_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Piwik link tracking is enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isLinkTrackingEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_LINK_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->isTrackingEnabled($store);
    }

    /**
     * Retrieve Piwik link tracking delay in milliseconds
     *
     * @param null|string|bool|int|Store $store
     * @return int
     */
    public function getLinkTrackingDelay($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_LINK_DELAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get provider code for Piwik user ID tracking
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getUserIdProviderCode($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UID_PROVIDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
