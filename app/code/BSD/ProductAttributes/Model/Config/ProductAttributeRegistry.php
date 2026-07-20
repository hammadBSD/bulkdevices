<?php

declare(strict_types=1);

namespace BSD\ProductAttributes\Model\Config;

class ProductAttributeRegistry
{
    public static function getDefinitions(): array
    {
        return array_merge(
            StorageAttributeOptions::getDefinitions(),
            AttributeWithValue02Options::getDefinitions()
        );
    }

    public static function getAttributeCodes(): array
    {
        return array_keys(self::getDefinitions());
    }

    public static function getStorageAttributeCodes(): array
    {
        return StorageAttributeOptions::getAttributeCodes();
    }
}
