<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_XSitemap_Block_Pages extends Mage_Core_Block_Template
{
    const XML_PATH_FILTER_PAGES = 'mageworx_seo/xsitemap/filter_pages';
    const XML_PATH_HOME_PAGE    = 'web/default/cms_home_page';

    protected $_homePage;

    protected function _construct()
    {
        $this->_homePage = Mage::getStoreConfig(self::XML_PATH_HOME_PAGE);
    }

    protected function _prepareLayout()
    {
        $filterPages = Mage::getStoreConfig(self::XML_PATH_FILTER_PAGES);
        $filterPages = explode(',', $filterPages);
        $collection  = Mage::getModel('cms/page')->getCollection();
        $collection->addStoreFilter(Mage::app()->getStore()->getId());
        $collection->addFieldToFilter('is_active', array('eq' => 1));
        $collection->addFieldToFilter('identifier', array('nin' => $filterPages));
        $this->setCollection($collection);
        return $this;
    }

    public function getItemUrl($page)
    {
        /*
        if ($this->_homePage == $page->getIdentifier()) {
            return Mage::getUrl(null);
        }
         */
        $version = Mage::getVersion();
        if (version_compare($version, '1.2', '<')) {
            return Mage::helper('xsitemap')->trailingSlash(Mage::getUrl($page->getIdentifier()));
        }
        return Mage::helper('xsitemap')->trailingSlash(Mage::getUrl(null, array('_direct' => $page->getIdentifier())));
    }

}
