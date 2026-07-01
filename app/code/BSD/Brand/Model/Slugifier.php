<?php

declare(strict_types=1);

namespace BSD\Brand\Model;

class Slugifier
{
    public function slugify(string $label): string
    {
        $slug = strtolower(trim($label));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}
