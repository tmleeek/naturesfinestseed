<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 *
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2016 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_XSitemap_Model_Mysql4_Catalog_Category_Ee_Xml extends MageWorx_XSitemap_Model_Mysql4_Catalog_Category_AbstractXml
{
    /**
     * Get category collection array
     *
     * @return array
     */
    public function getCollection($storeId)
    {
        $categories = array();

        /* @var $store Mage_Core_Model_Store */
        $store = Mage::app()->getStore($storeId);

        if (!$store) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from($this->getMainTable())
            ->where($this->getIdFieldName() . '=?', $store->getRootCategoryId());
        $categoryRow   = $this->_getWriteAdapter()->fetchRow($this->_select);

        if (!$categoryRow) {
            return false;
        }

        $this->_select = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName()));

        $this->_addFilter($storeId, 'is_active', 0);

        $queryInactive = $this->_getWriteAdapter()->query($this->_select);
        $allRows       = $queryInactive->fetchAll();

        if ($categoryRow['path'] != '') {
            $categoryRow['path'] .= '/';
        }

        $condition = '';
        foreach ($allRows as $row) {
            $condition .= 'path not like \'%/' . $row['entity_id'] . '/%\' AND path not like \'' . $categoryRow['path'] . $row['entity_id'] . '/%\' AND ';
        }
        $condition = substr($condition, 0, count($condition) - 6);
        if ($condition) {
            $condition = ' AND ' . $condition;
        }

        $newRows = array();
        foreach ($allRows as $row) {
            $newRows[] = $row['entity_id'];
        }
        $condition2 = implode(',', $newRows);
        if (strlen($condition2) > 0) {
            $condition2 = ' AND parent_id NOT IN(' . $condition2 . ')';
        }
        else {
            $condition2 = '';
        }

        $read      = $this->_getReadAdapter();
        $urlSuffix = Mage::helper('catalog/category')->getCategoryUrlSuffix($storeId);

        if($urlSuffix){
            $urlSuffix = '.' . $urlSuffix;
        }else{
            $urlSuffix = '';
        }

        $urConditions1 = array(
            'e.entity_id=eccr.category_id',
            $this->_getWriteAdapter()->quoteInto("eccr.store_id IN(0, ?)",
                $storeId)
        );

        $urConditions2 = array(
            'eccr.url_rewrite_id=eur.url_rewrite_id',
            $this->_getWriteAdapter()->quoteInto('eur.is_system=?', 1)
        );

        $this->_select = $read->select()
            ->from(array('e' => $this->getMainTable()), array($this->getIdFieldName(), 'path', 'level'))
            ->joinLeft(
                array('eccr' => $this->getTable('enterprise_catalog/category')), join(' AND ', $urConditions1)
            )
            ->joinLeft(
                array('eur' => $this->getTable('enterprise_urlrewrite/url_rewrite')), join(' AND ', $urConditions2),
                array('url' => $this->_getWriteAdapter()->getConcatSql(array('request_path', "'$urlSuffix'")))
            )
            ->where('e.path LIKE ?' . $condition . $condition2, $categoryRow['path'] . '%')
            ->order('level ASC');

        $excludeAttr = Mage::getSingleton('catalog/category')->getResource()->getAttribute('exclude_from_sitemap');
        if ($excludeAttr) {
            $this->_select->joinLeft(
                    array('exclude_tbl' => $excludeAttr->getBackend()->getTable()),
                    'exclude_tbl.entity_id = e.entity_id AND exclude_tbl.attribute_id = ' . $excludeAttr->getAttributeId() . ' AND exclude_tbl.store_id = 0',
                    array()
                )
                ->where('exclude_tbl.value=0 OR exclude_tbl.value IS NULL');
        }

        $this->_addFilter($storeId, 'is_active', 1);

        $query = $read->query($this->_select);
        while ($row   = $query->fetch()) {
            $category                       = $this->_prepareCategory($row);
            $categories[$category->getId()] = $category;
        }

        return $categories;
    }
}