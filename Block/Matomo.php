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

namespace Chessio\Matomo\Block;

/**
 * Matomo page block
 *
 */
class Matomo extends \Magento\Framework\View\Element\Template
{

    /**
     * JSON encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Matomo tracker model
     *
     * @var \Chessio\Matomo\Model\Tracker $_tracker
     */
    protected $_tracker;

    /**
     * Matomo data helper
     *
     * @var \Chessio\Matomo\Helper\Data
     */
    protected $_dataHelper = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Chessio\Matomo\Model\Tracker $tracker
     * @param \Chessio\Matomo\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Chessio\Matomo\Model\Tracker $tracker,
        \Chessio\Matomo\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_tracker = $tracker;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Matomo tracker actions
     *
     * @return \Chessio\Matomo\Model\Tracker
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
            'matomo_track_page_view_before',
            ['block' => $this, 'tracker' => $tracker]
        );

        if (!$this->getSkipTrackPageView()) {
            $tracker->trackPageView();
        }

        $this->_eventManager->dispatch(
            'matomo_track_page_view_after',
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
        if ($this->isContainerEnabled()) {
            $result = [];
        } else {
            $result = [
                'scriptUrl'  => $this->getScriptUrl(),
                'trackerUrl' => $this->getTrackerUrl(),
                'siteId'     => $this->getSiteId(),
            ];
        }
        $result['isContainerEnabled'] = $this->_dataHelper->isContainerEnabled();
        $result['actions'] = $this->getTracker()->toArray();

        return $result;
    }

    /**
     * Check if Matomo Tag Manager Container is enabled
     * @return string
     */
    public function isContainerEnabled()
    {
        return $this->_dataHelper->isContainerEnabled();
    }

    /**
     * Get Matomo Tag Manager Container URL
     * @return string
     */
    public function getContainerUrl()
    {
        return $this->_dataHelper->getContainerUrl();
    }

    /**
     * Get Matomo JS URL
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->_dataHelper->getJsScriptUrl();
    }

    /**
     * Get Matomo tracker URL
     *
     * @return string
     */
    public function getTrackerUrl()
    {
        return $this->_dataHelper->getPhpScriptUrl();
    }

    /**
     * Get Matomo site ID
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
        $params = [
            'idsite' => $this->getSiteId(),
            'rec'    => 1,
            'url'    => $this->_urlBuilder->getCurrentUrl()
        ];
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
     * Generate Matomo tracking script
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
