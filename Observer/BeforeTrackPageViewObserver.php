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
 * Observer for `piwik_track_page_view_before'
 *
 */
class BeforeTrackPageViewObserver implements ObserverInterface
{

    /**
     * Piwik data helper
     *
     * @var \Partnerpages\Piwik\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Constructor
     *
     * @param \Partnerpages\Piwik\Helper\Data $dataHelper
     */
    public function __construct(\Partnerpages\Piwik\Helper\Data $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Push additional actions to tracker before `trackPageView' is added
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Partnerpages\Piwik\Observer\BeforeTrackPageViewObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $tracker = $observer->getEvent()->getTracker();
        /* @var $tracker \Partnerpages\Piwik\Model\Tracker */

        $this->_pushLinkTracking($tracker);

        return $this;
    }

    /**
     * Push link tracking options to given tracker
     *
     * @param \Partnerpages\Piwik\Model\Tracker $tracker
     * @return \Partnerpages\Piwik\Observer\BeforeTrackPageViewObserver
     */
    protected function _pushLinkTracking(\Partnerpages\Piwik\Model\Tracker $tracker)
    {
        if ($this->_dataHelper->isLinkTrackingEnabled()) {
            $tracker->enableLinkTracking(true);
            $delay = $this->_dataHelper->getLinkTrackingDelay();
            if ($delay > 0) {
                $tracker->setLinkTrackingTimer($delay);
            }
        }
        return $this;
    }
}
