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

namespace Henhed\Piwik\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `controller_action_layout_render_before_catalogsearch_result_index'
 *
 * @see http://developer.piwik.org/guides/tracking-javascript-guide#internal-search-tracking
 */
class SearchResultObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Henhed\Piwik\Model\Tracker
     */
    protected $_piwikTracker;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Search query factory
     *
     * @var \Magento\Search\Model\QueryFactory $_queryFactory
     */
    protected $_queryFactory;

    /**
     * Current view
     *
     * @var \Magento\Framework\App\ViewInterface $_view
     */
    protected $_view;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\Model\Tracker $piwikTracker
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\App\ViewInterface $view
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_dataHelper = $dataHelper;
        $this->_queryFactory = $queryFactory;
        $this->_view = $view;
    }

    /**
     * Push `trackSiteSearch' to tracker on search result page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\SearchResultObserver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isTrackingEnabled()) {
            return $this;
        }

        $query = $this->_queryFactory->get();
        $piwikBlock = $this->_view->getLayout()->getBlock('piwik.tracker');
        /** @var \Magento\Search\Model\Query $query */
        /** @var \Henhed\Piwik\Block\Piwik $piwikBlock */

        $keyword = $query->getQueryText();
        $resultsCount = $query->getNumResults();

        if ($resultsCount === null) {
            // If this is a new search query the result count hasn't been saved
            // yet so we have to fetch it from the search result block instead.
            $resultBock = $this->_view->getLayout()->getBlock('search.result');
            /** @var \Magento\CatalogSearch\Block\Result $resultBock */
            if ($resultBock) {
                $resultsCount = $resultBock->getResultCount();
            }
        }

        if ($resultsCount === null) {
            $this->_piwikTracker->trackSiteSearch($keyword);
        } else {
            $this->_piwikTracker->trackSiteSearch(
                $keyword,
                false,
                (int) $resultsCount
            );
        }

        if ($piwikBlock) {
            // Don't push `trackPageView' when `trackSiteSearch' is set
            $piwikBlock->setSkipTrackPageView(true);
        }

        return $this;
    }
}
