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

namespace Partnerpages\Piwik\Block;

/**
 * Piwik page block
 *
 */
class Piwik extends \Magento\Framework\View\Element\Template
{

    /**
     * JSON encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Piwik tracker model
     *
     * @var \Partnerpages\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Piwik data helper
     *
     * @var \Partnerpages\Piwik\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Partnerpages\Piwik\Model\Tracker $tracker
     * @param \Partnerpages\Piwik\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Partnerpages\Piwik\Model\Tracker $tracker,
        \Partnerpages\Piwik\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_tracker = $tracker;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Piwik tracker actions
     *
     * @return \Partnerpages\Piwik\Model\Tracker
     */
    public function getTracker()
    {
        return $this->_tracker;
    }

    /**
     * Populate tracker with actions before rendering
     *
     * @return void
     */
    protected function _prepareTracker()
    {
        $tracker = $this->getTracker();

        $this->_eventManager->dispatch(
            'piwik_track_page_view_before',
            ['block' => $this, 'tracker' => $tracker]
        );

        if (!$this->getSkipTrackPageView()) {
            $tracker->trackPageView();
        }

        $this->_eventManager->dispatch(
            'piwik_track_page_view_after',
            ['block' => $this, 'tracker' => $tracker]
        );
    }

    /**
     * Get javascript tracker options
     *
     * @return array
     */
    public function getJsOptions()
    {
        return [
            'scriptUrl'  => $this->getScriptUrl(),
            'trackerUrl' => $this->getTrackerUrl(),
            'siteId'     => $this->getSiteId(),
            'actions'    => $this->getTracker()->toArray()
        ];
    }

    /**
     * Get Piwik JS URL
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->_dataHelper->getBaseUrl() . 'piwik.js';
    }

    /**
     * Get Piwik tracker URL
     *
     * @return string
     */
    public function getTrackerUrl()
    {
        return $this->_dataHelper->getBaseUrl() . 'piwik.php';
    }

    /**
     * Get Piwik site ID
     *
     * @return int
     */
    public function getSiteId()
    {
        return $this->_dataHelper->getSiteId();
    }

    /**
     * Get tracking pixel URL
     *
     * @return string
     */
    public function getTrackingPixelUrl()
    {
        $params = array(
            'idsite' => $this->getSiteId(),
            'rec'    => 1,
            'url'    => $this->_urlBuilder->getCurrentUrl()
        );
        return $this->getTrackerUrl() . '?' . http_build_query($params);
    }

    /**
     * Encode data to a JSON string
     *
     * @param mixed $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Generate Piwik tracking script
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_dataHelper->isTrackingEnabled()) {
            $this->_prepareTracker();
            return parent::_toHtml();
        }
        return '';
    }
}
