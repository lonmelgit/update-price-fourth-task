<?php

namespace Update\Price\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{


    public function updatePrices($entityId, $price, $storeId = 0)
    {
        $connection = $this->_getWriteConnection();

        $attributeId = $this->_getAttributeId('price');

        $sql = "INSERT INTO " . $this->_getTableName('catalog_product_entity_decimal') . " (attribute_id, store_id, entity_id, value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)";
        $connection->query(
            $sql,
            [
                $attributeId,
                $storeId,
                $entityId,
                $price
            ]
        );
    }

    public function _getEntityTypeId($entityTypeCode)
    {
        $connection = $this->_getConnection('core_read');
        $sql = "SELECT entity_type_id FROM " . $this->_getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
        return $connection->fetchOne(
            $sql,
            [
                $entityTypeCode
            ]
        );
    }

    public function _getResourceConnection()
    {
        global $obj;
        return $obj->get('Magento\Framework\App\ResourceConnection');
    }


    public function _getReadConnection()
    {
        return $this->_getConnection('core_read');
    }


    public function _getWriteConnection()
    {
        return $this->_getConnection('core_write');
    }


    public function _getConnection($type = 'core_read')
    {
        return $this->_getResourceConnection()->getConnection($type);
    }

    public function _getAttributeId($attributeCode)
    {
        $connection = $this->_getReadConnection();
        $sql = "SELECT attribute_id FROM " . $this->_getTableName('eav_attribute') . " WHERE entity_type_id = ? AND attribute_code = ?";
        return $connection->fetchOne(
            $sql,
            [
                $this->_getEntityTypeId('catalog_product'),
                $attributeCode
            ]
        );
    }

    public function _getTableName($tableName)
    {
        return $this->_getResourceConnection()->getTableName($tableName);
    }
}