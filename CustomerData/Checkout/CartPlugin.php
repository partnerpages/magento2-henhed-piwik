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

namespace Partnerpages\Piwik\CustomerData\Checkout;

/**
 * Plugin for \Magento\Checkout\CustomerData\Cart
 *
 */
class CartPlugin
{

    /**
     * Checkout session instance
     *
     * @var \Magento\Checkout\Model\Session $_checkoutSession
     */
    protected $_checkoutSession;

    /**
     * Piwik data helper
     *
     * @var \Partnerpages\Piwik\Helper\Data $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Tracker helper
     *
     * @var \Partnerpages\Piwik\Helper\Tracker $_trackerHelper
     */
    protected $_trackerHelper;

    /**
     * Tracker factory
     *
     * @var \Partnerpages\Piwik\Model\TrackerFactory $_trackerFactory
     */
    protected $_trackerFactory;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Partnerpages\Piwik\Helper\Data $dataHelper
     * @param \Partnerpages\Piwik\Helper\Tracker $trackerHelper
     * @param \Partnerpages\Piwik\Model\TrackerFactory $trackerFactory
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Partnerpages\Piwik\Helper\Data $dataHelper,
        \Partnerpages\Piwik\Helper\Tracker $trackerHelper,
        \Partnerpages\Piwik\Model\TrackerFactory $trackerFactory
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
        $this->_trackerHelper = $trackerHelper;
        $this->_trackerFactory = $trackerFactory;
    }

    /**
     * Add `trackEcommerceCartUpdate' checkout cart customer data
     *
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
        $result
    ) {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $quote = $this->_checkoutSession->getQuote();
            if ($quote->getId()) {
                $tracker = $this->_trackerFactory->create();
                $this->_trackerHelper->addQuote($quote, $tracker);
                $result['piwikActions'] = $tracker->toArray();
            }
        }
        return $result;
    }
}
