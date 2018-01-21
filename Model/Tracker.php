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

namespace Henhed\Piwik\Model;

/**
 * Piwik tracker model
 *
 * @method Tracker trackEvent(string $category, string $action,
 *                            string $name = null, int $value = null)
 * Logs an event with an event category (Videos, Music, Games...), an event
 * action (Play, Pause, Duration, Add Playlist, Downloaded, Clicked...), and an
 * optional event name and optional numeric value.
 *
 * @method Tracker trackPageView(string $customTitle = null)
 * Logs a visit to this page
 *
 * @method Tracker trackSiteSearch(string $keyword, string $category = null,
 *                                 int $resultsCount = null)
 * Log an internal site search for a specific keyword, in an optional category,
 * specifying the optional count of search results in the page.
 *
 * @method Tracker trackGoal(int $idGoal, float $customRevenue = null)
 * Manually log a conversion for the numeric goal ID, with an optional numeric
 * custom revenue customRevenue.
 *
 * @method Tracker trackLink(string $url, string $linkType)
 * Manually log a click from your own code. url is the full URL which is to be
 * tracked as a click. linkType can either be 'link' for an outlink or
 * 'download' for a download.
 *
 * @method Tracker trackAllContentImpressions()
 * Scans the entire DOM for all content blocks and tracks all impressions once
 * the DOM ready event has been triggered.
 *
 * @method Tracker trackVisibleContentImpressions(bool $checkOnSroll,
 *                                                int $timeIntervalInMs)
 * Scans the entire DOM for all content blocks as soon as the page is loaded.
 * It tracks an impression only if a content block is actually visible.
 *
 * @method Tracker trackContentImpression(string $contentName,
 *                                        string $contentPiece,
 *                                        string $contentTarget)
 * Tracks a content impression using the specified values.
 *
 * @method Tracker trackContentInteraction(string $contentInteraction,
 *                                         string $contentName,
 *                                         string $contentPiece,
 *                                         string $contentTarget)
 * Tracks a content interaction using the specified values.
 *
 * @method Tracker logAllContentBlocksOnPage()
 * Log all found content blocks within a page to the console. This is useful to
 * debug / test content tracking.
 *
 * @method Tracker enableLinkTracking(bool $enable = null)
 * Install link tracking on all applicable link elements. Set the enable
 * parameter to true to use pseudo click-handler (treat middle click and open
 * contextmenu as left click). A right click (or any click that opens the
 * context menu) on a link will be tracked as clicked even if "Open in new tab"
 * is not selected. If "false" (default), nothing will be tracked on open
 * context menu or middle click.
 *
 * @method Tracker enableHeartBeatTimer(int $delayInSeconds)
 * Install a Heart beat timer that will regularly send requests to Piwik (every
 * delayInSeconds seconds) in order to better measure the time spent on the
 * page. These requests will be sent only when the user is actively viewing the
 * page (when the tab is active and in focus). These requests will not track
 * additional actions or pageviews.
 *
 * @method Tracker setDocumentTitle(string $title)
 * Override document.title
 *
 * @method Tracker setDomains(array $domains)
 * Set array of hostnames or domains to be treated as local. For wildcard
 * subdomains, you can use: '.example.com' or '*.example.com'. You can also
 * specify a path along a domain: '*.example.com/subsite1'
 *
 * @method Tracker setCustomUrl(string $customUrl)
 * Override the page's reported URL
 *
 * @method Tracker setReferrerUrl(string $referrerUrl)
 * Override the detected Http-Referer
 *
 * @method Tracker setSiteId(int $siteId)
 * Specify the website ID
 *
 * @method Tracker setApiUrl(string $apiUrl)
 * Specify the Piwik HTTP API URL endpoint. Points to the root directory of
 * piwik, e.g. http://piwik.example.org/ or https://example.org/piwik/. This
 * function is only useful when the 'Overlay' report is not working. By default
 * you do not need to use this function.
 *
 * @method Tracker setTrackerUrl(string $trackerUrl)
 * Specify the Piwik server URL.
 *
 * @method Tracker setDownloadClasses(string|array $downloadClasses)
 * Set classes to be treated as downloads (in addition to piwik_download)
 *
 * @method Tracker setDownloadExtensions(string|array $downloadExtensions)
 * Set list of file extensions to be recognized as downloads. Example: 'doc' or
 * ['doc', 'xls']
 *
 * @method Tracker addDownloadExtensions(string|array $downloadExtensions)
 * Specify additional file extensions to be recognized as downloads. Example:
 * 'doc' or ['doc', 'xls']
 *
 * @method Tracker removeDownloadExtensions(string|array $downloadExtensions)
 * Specify file extensions to be removed from the list of download file
 * extensions. Example: 'doc' or ['doc', 'xls']
 *
 * @method Tracker setIgnoreClasses(string|array $ignoreClasses)
 * Set classes to be ignored if present in link (in addition to piwik_ignore)
 *
 * @method Tracker setLinkClasses(string|array $linkClasses)
 * Set classes to be treated as outlinks (in addition to piwik_link)
 *
 * @method Tracker setLinkTrackingTimer(int $linkTrackingTimer)
 * Set delay for link tracking in milliseconds.
 *
 * @method Tracker discardHashTag(bool $flag)
 * Set to true to not record the hash tag (anchor) portion of URLs
 *
 * @method Tracker setGenerationTimeMs(int $generationTime)
 * By default Piwik uses the browser DOM Timing API to accurately determine the
 * time it takes to generate and download the page. You may overwrite the value
 * by specifying a milliseconds value here.
 *
 * @method Tracker appendToTrackingUrl(string $appendToUrl)
 * Appends a custom string to the end of the HTTP request to piwik.php?
 *
 * @method Tracker setDoNotTrack(bool $flag)
 * Set to true to not track users who opt out of tracking using Mozilla's
 * (proposed) Do Not Track setting.
 *
 * @method Tracker disableCookies()
 * Disables all first party cookies. Existing Piwik cookies for this websites
 * will be deleted on the next page view.
 *
 * @method Tracker deleteCookies()
 * Deletes the tracking cookies currently set (this is useful when creating new
 * visits)
 *
 * @method Tracker killFrame()
 * Enables a frame-buster to prevent the tracked web page from being
 * framed/iframed.
 *
 * @method Tracker redirectFile(string $url)
 * Forces the browser load the live URL if the tracked web page is loaded from a
 * local file (e.g., saved to someone's desktop).
 *
 * @method Tracker setHeartBeatTimer(int $minimumVisitLength,
 *                                   int $heartBeatDelay)
 * Records how long the page has been viewed if the $minimumVisitLength (in
 * seconds) is attained; the $heartBeatDelay determines how frequently to update
 * the server.
 *
 * @method Tracker setUserId(string $userId)
 * Sets a User ID to this user (such as an email address or a username).
 *
 * @method Tracker setCustomVariable(int $index, string $name, string $value,
 *                                   string $scope)
 * Set a custom variable.
 *
 * @method Tracker deleteCustomVariable(int $index, string $scope)
 * Delete a custom variable.
 *
 * @method Tracker storeCustomVariablesInCookie()
 * When called then the Custom Variables of scope "visit" will be stored
 * (persisted) in a first party cookie for the duration of the visit. This is
 * useful if you want to call getCustomVariable later in the visit. (by default
 * custom variables are not stored on the visitor's computer.)
 *
 * @method Tracker setCustomDimension(int $customDimensionId,
 *                                    string $customDimensionValue)
 * Set a custom dimension. (requires Piwik 2.15.1 + Custom Dimensions plugin)
 *
 * @method Tracker deleteCustomDimension(int customDimensionId)
 * Delete a custom dimension. (requires Piwik 2.15.1 + Custom Dimensions plugin)
 *
 * @method Tracker setCampaignNameKey(string $name)
 * Set campaign name parameter(s).
 *
 * @method Tracker setCampaignKeywordKey(string $keyword)
 * Set campaign keyword parameter(s).
 *
 * @method Tracker setConversionAttributionFirstReferrer(bool $flag)
 * Set to true to attribute a conversion to the first referrer. By default,
 * conversion is attributed to the most recent referrer.
 *
 * @method Tracker setEcommerceView(string $sku, string $productName,
 *                                  string $categoryName, float $price)
 * Sets the current page view as a product or category page view. When you call
 * setEcommerceView it must be followed by a call to trackPageView to record the
 * product or category page view.
 *
 * @method Tracker addEcommerceItem(string $sku, string $productName = null,
 *                                  string $categoryName = null,
 *                                  float $price = null, float $quantity = null)
 * Adds a product into the ecommerce order. Must be called for each product in
 * the order.
 *
 * @method Tracker trackEcommerceCartUpdate(float $grandTotal)
 * Tracks a shopping cart. Call this function every time a user is adding,
 * updating or deleting a product from the cart.
 *
 * @method Tracker trackEcommerceOrder(string $orderId, float $grandTotal,
 *                                     float $subTotal = null,
 *                                     float $tax = null,
 *                                     float $shipping = null,
 *                                     float $discount = null)
 * Tracks an Ecommerce order, including any ecommerce item previously added to
 * the order. orderId and grandTotal (ie. revenue) are required parameters.
 *
 * @method Tracker setCookieNamePrefix(string $prefix)
 * The default prefix is 'pk'.
 *
 * @method Tracker setCookieDomain(string $domain)
 * The default is the document domain; if your web site can be visited at both
 * www.example.com and example.com, you would use: '.example.com' or
 * '*.example.com'
 *
 * @method Tracker setCookiePath(string $path)
 * The default is '/'.
 *
 * @method Tracker setVisitorCookieTimeout(int $seconds)
 * The default is 13 months
 *
 * @method Tracker setReferralCookieTimeout(int $seconds)
 * The default is 6 months
 *
 * @method Tracker setSessionCookieTimeout(int $seconds)
 * The default is 30 minutes
 *
 * @see http://developer.piwik.org/api-reference/tracking-javascript
 */
class Tracker
{

    /**
     * Action items
     *
     * @var \Henhed\Piwik\Model\Tracker\Action[] $_actions
     */
    protected $_actions = [];

    /**
     * Tracker action factory instance
     *
     * @var \Henhed\Piwik\Model\Tracker\ActionFactory $_actionFactory
     */
    protected $_actionFactory;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\Model\Tracker\ActionFactory $actionFactory
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker\ActionFactory $actionFactory
    ) {
        $this->_actionFactory = $actionFactory;
    }

    /**
     * Push an action to this tracker
     *
     * @param \Henhed\Piwik\Model\Tracker\Action $action
     * @return \Henhed\Piwik\Model\Tracker
     */
    public function push(Tracker\Action $action)
    {
        $this->_actions[] = $action;
        return $this;
    }

    /**
     * Get all actions in this tracker
     *
     * @return \Henhed\Piwik\Model\Tracker\Action[]
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Get an array representation of this tracker
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->getActions() as $action) {
            $array[] = $action->toArray();
        }
        return $array;
    }

    /**
     * Magic action push function
     *
     * @param string $name
     * @param array $arguments
     * @return \Henhed\Piwik\Model\Tracker
     */
    public function __call($name, $arguments)
    {
        return $this->push($this->_actionFactory->create([
            'name' => $name,
            'args' => $arguments
        ]));
    }
}
