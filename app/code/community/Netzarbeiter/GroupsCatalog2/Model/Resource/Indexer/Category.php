<?php
/**
 * Netzarbeiter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupsCatalog2
 * @copyright  Copyright (c) 2014 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netzarbeiter_GroupsCatalog2_Model_Resource_Indexer_Category
    extends Netzarbeiter_GroupsCatalog2_Model_Resource_Indexer_Abstract
{
    /**
     * Initialize with table name and id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('netzarbeiter_groupscatalog2/category_index', 'id');
    }

    /**
     * Handle reindexing of single entity save events
     *
     * @param Mage_Index_Model_Event $event
     * @return Netzarbeiter_GroupsCatalog2_Model_Resource_Indexer_Category
     * @see Netzarbeiter_GroupsCatalog2_Model_Indexer_Abstract::_processEvent()
     */
    public function catalogCategorySave(Mage_Index_Model_Event $event)
    {
        $this->_reindexEntity($event);
        return $this;
    }

    /**
     * Handle reindexing of entity mass action events
     *
     * @param Mage_Index_Model_Event $event
     * @return Netzarbeiter_GroupsCatalog2_Model_Resource_Indexer_Category
     * @see Netzarbeiter_GroupsCatalog2_Model_Indexer_Abstract::_processEvent()
     */
    public function catalogCategoryMassAction(Mage_Index_Model_Event $event)
    {
        $this->_reindexEntity($event);
        return $this;
    }

    /**
     * Return this indexers entity type code
     *
     * @return string
     */
    protected function _getEntityTypeCode()
    {
        return Mage_Catalog_Model_Category::ENTITY;
    }

    /**
     * Apply a filter
     *
     * @param array $data Insert data
     */
    protected function _applyFilterToData(array &$data)
    {
        if ($this->_helper()->getConfig('show_empty_categories')) return;

        $this->_categoryProducts = array();
        foreach($data as $key => $values) {
            if(empty($this->_categoryProducts[$values['catalog_entity_id']])) {
                $category = Mage::getModel('catalog/category')->load($values['catalog_entity_id']);
                $productCollection = Mage::getResourceModel('catalog/product_collection')
                    ->addCategoryFilter($category);
                foreach($productCollection as $product) {
                    $this->_categoryProducts[$values['catalog_entity_id']][] = $product->getId();
                }
            }
            $visibleItems = Mage::getResourceSingleton('netzarbeiter_groupscatalog2/filter')
                ->getVisibleIdsFromEntityIdList(
                    Mage_Catalog_Model_Product::ENTITY,
                    $this->_categoryProducts[$values['catalog_entity_id']],
                    $values['store_id'],
                    $values['group_id']
                );

            if(empty($visibleItems)) {
                unset($data[$key]);
            }
        }
    }
}
