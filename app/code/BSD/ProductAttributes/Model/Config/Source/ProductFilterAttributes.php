<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Model\Config\Source;

use BSD\ProductAttributes\Model\Config\ProductAttributeRegistry;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ProductFilterAttributes extends AbstractSource
{
    public function getAllOptions(): array
    {
        if ($this->_options !== null) {
            return $this->_options;
        }

        $this->_options = [];
        foreach (ProductAttributeRegistry::getDefinitions() as $code => $definition) {
            $this->_options[] = [
                'value' => $code,
                'label' => (string) $definition['label'],
            ];
        }

        return $this->_options;
    }
}
