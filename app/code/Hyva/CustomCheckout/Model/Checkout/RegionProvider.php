<?php
declare(strict_types=1);

namespace Hyva\CustomCheckout\Model\Checkout;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class RegionProvider
{
    public function __construct(
        private readonly RegionCollectionFactory $regionCollectionFactory,
    ) {
    }

    public function getRegionsForCountry(string $countryId): array
    {
        if ($countryId === '') {
            return [];
        }

        $collection = $this->regionCollectionFactory->create();
        $collection->addCountryFilter($countryId);
        $collection->load();

        $regions = [];
        foreach ($collection as $region) {
            $regions[] = [
                'id' => (string) $region->getRegionId(),
                'code' => (string) $region->getCode(),
                'name' => (string) $region->getName(),
            ];
        }

        return $regions;
    }
}
