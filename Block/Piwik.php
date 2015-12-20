<?php
/**
 * Copyright 2015 Henrik Hedelund
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

namespace Henhed\Piwik\Block;

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
     * @var \Henhed\Piwik\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Piwik data helper
     *
     * @var \Henhed\Piwik\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Henhed\Piwik\Model\Tracker $tracker
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Henhed\Piwik\Model\Tracker $tracker,
        \Henhed\Piwik\Helper\Data $dataHelper,
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
     * @return \Henhed\Piwik\Model\Tracker
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
        $this->getTracker()
            ->setTrackerUrl($this->getTrackerUrl())
            ->setSiteId($this->_dataHelper->getSiteId())
            ->trackPageView();
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
     * Get tracking pixel URL
     *
     * @return string
     */
    public function getTrackingPixelUrl()
    {
        $params = array(
            'idsite' => $this->_dataHelper->getSiteId(),
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
