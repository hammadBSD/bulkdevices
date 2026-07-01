<?php

declare(strict_types=1);

namespace BSD\Brand\Model;

use Magento\Framework\DataObject;

class Brand extends DataObject
{
    public function getOptionId(): int
    {
        return (int) $this->getData('option_id');
    }

    public function getLabel(): string
    {
        return (string) $this->getData('label');
    }

    public function getSlug(): string
    {
        return (string) $this->getData('slug');
    }

    public function getImageUrl(): ?string
    {
        $url = $this->getData('image_url');
        return is_string($url) && $url !== '' ? $url : null;
    }
}
