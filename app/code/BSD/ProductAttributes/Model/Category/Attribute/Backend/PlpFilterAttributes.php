<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Multiselect backend for string attribute codes (not numeric option IDs).
 */
class PlpFilterAttributes extends AbstractBackend
{
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);

        if (is_array($data)) {
            $data = array_values(array_filter($data, static function ($value): bool {
                return $value === '0' || ($value !== null && $value !== '');
            }));
            $object->setData($attributeCode, $data === [] ? null : implode(',', $data));
        } elseif (is_string($data) && trim($data) === '') {
            $object->setData($attributeCode, null);
        }

        return parent::beforeSave($object);
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);

        if (is_string($data) && $data !== '') {
            $object->setData(
                $attributeCode,
                array_values(array_filter(array_map('trim', explode(',', $data))))
            );
        }

        return parent::afterLoad($object);
    }
}
