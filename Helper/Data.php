<?php
/**
 * Copyright 2016 Henrik Hedelund
 *
 * This file is part of Partnerpages_Piwik.
 *
 * Partnerpages_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Partnerpages_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Partnerpages_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Partnerpages\Piwik\Helper;

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
    const XML_PATH_SITE_ID = 'piwik/tracking/site_id';
    const XML_PATH_LINK_ENABLED = 'piwik/tracking/link_enabled';
    const XML_PATH_LINK_DELAY = 'piwik/tracking/link_delay';

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
        return $this->scopeConfig->getValue(
            self::XML_PATH_HOSTNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
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
        if (is_null($secure)) {
            $secure = $this->_request->isSecure();
        }
        $host = rtrim($this->getHostname($store), '/');
        return ($secure ? 'https://' : 'http://') . $host . '/';
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
}
