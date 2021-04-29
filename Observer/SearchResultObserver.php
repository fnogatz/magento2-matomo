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

namespace Chessio\Matomo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `controller_action_layout_render_before_catalogsearch_result_index'
 *
 * @see http://developer.matomo.org/guides/tracking-javascript-guide#internal-search-tracking
 */
class SearchResultObserver implements ObserverInterface
{

    /**
     * Matomo tracker instance
     *
     * @var \Chessio\Matomo\Model\Tracker
     */
    protected $_matomoTracker;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data $_dataHelper
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
     * @param \Chessio\Matomo\Model\Tracker $matomoTracker
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Chessio\Matomo\Model\Tracker $matomoTracker,
        \Chessio\Matomo\Helper\Data $dataHelper,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\App\ViewInterface $view
    ) {
        $this->_matomoTracker = $matomoTracker;
        $this->_dataHelper = $dataHelper;
        $this->_queryFactory = $queryFactory;
        $this->_view = $view;
    }

    /**
     * Push `trackSiteSearch' to tracker on search result page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Chessio\Matomo\Observer\SearchResultObserver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isTrackingEnabled()) {
            return $this;
        }

        $query = $this->_queryFactory->get();
        $matomoBlock = $this->_view->getLayout()->getBlock('matomo.tracker');
        /** @var \Magento\Search\Model\Query $query */
        /** @var \Chessio\Matomo\Block\Matomo $matomoBlock */

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
            $this->_matomoTracker->trackSiteSearch($keyword);
        } else {
            $this->_matomoTracker->trackSiteSearch(
                $keyword,
                false,
                (int) $resultsCount
            );
        }

        if ($matomoBlock) {
            // Don't push `trackPageView' when `trackSiteSearch' is set
            $matomoBlock->setSkipTrackPageView(true);
        }

        return $this;
    }
}
