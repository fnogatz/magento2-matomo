<?php
/**
 * Copyright 2016-2017 Henrik Hedelund
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
 * Observer for `catalog_controller_product_view'
 *
 */
class ProductViewObserver implements ObserverInterface
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

    protected $storeManager;
    
    protected $product;

    /**
     * Constructor
     *
     * @param \Henhed\Piwik\Model\Tracker $piwikTracker
     * @param \Henhed\Piwik\Helper\Data $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Henhed\Piwik\Model\Tracker $piwikTracker,
        \Henhed\Piwik\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_piwikTracker = $piwikTracker;
        $this->_dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->product = $productFactory;
    }

    /**
     * Push EcommerceView to tracker on product view page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Henhed\Piwik\Observer\ProductViewObserver
     */
     public function execute(\Magento\Framework\Event\Observer $observer)
     {
         
         if (!$this->_dataHelper->isTrackingEnabled()) {
             return $this;
         }
         $store = $this->storeManager->getStore()->getCode();
         $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
         $product = $this->product->create()->load($observer->getEvent()->getProduct()->getId());
 
         $category = $product->getCategoryCollection()->addAttributeToSelect('name')->getLastItem();
 
         $this->_piwikTracker->setEcommerceView(
             $product->getSku(),
             $product->getName(),
             $category
                 ? $category->getName()
                 : false,
             $product->getFinalPrice()
         );
         $this->storeManager->setCurrentStore($store);
         return $this;
 
     }
}
