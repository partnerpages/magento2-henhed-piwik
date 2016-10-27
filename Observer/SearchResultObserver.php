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

namespace Partnerpages\Piwik\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for `controller_action_layout_render_before_catalogsearch_result_index'
 *
 * @link http://developer.piwik.org/guides/tracking-javascript-guide#internal-search-tracking
 */
class SearchResultObserver implements ObserverInterface
{

    /**
     * Piwik tracker instance
     *
     * @var \Partnerpages\Piwik\Model\Tracker
     */
    protected $_piwikTracker;

    /**
     * Piwik data helper
     *
     * @var \Partnerpages\Piwik\Helper\Data $_dataHelper
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
     * @param \Partnerpages\Piwik\Model\Tracker $piwikTracker
     * @param \Partnerpages\Piwik\Helper\Data $dataHelper
     * @param \Magento\Search\Model\QueryFactory $queryFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    public function __construct(
        \Partnerpages\Piwik\Model\Tracker $piwikTracker,
        \Partnerpages\Piwik\Helper\Data $dataHelper,
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
     * @return \Partnerpages\Piwik\Observer\SearchResultObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_dataHelper->isTrackingEnabled()) {
            return $this;
        }

        $query = $this->_queryFactory->get();
        $piwikBlock = $this->_view->getLayout()->getBlock('piwik.tracker');
        /* @var $query \Magento\Search\Model\Query */
        /* @var $piwikBlock \Partnerpages\Piwik\Block\Piwik */

        $keyword = $query->getQueryText();
        $resultsCount = $query->getNumResults();

        if (is_null($resultsCount)) {
            // If this is a new search query the result count hasn't been saved
            // yet so we have to fetch it from the search result block instead.
            $resultBock = $this->_view->getLayout()->getBlock('search.result');
            /* @var $resultBock \Magento\CatalogSearch\Block\Result */
            if ($resultBock) {
                $resultsCount = $resultBock->getResultCount();
            }
        }

        if (is_null($resultsCount)) {
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
