<?php

declare(strict_types=1);

namespace BSD\Storefront\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class TopCategories implements ArgumentInterface
{
    private const MEDIA_BASE = 'https://bulkdevices.com/media/wysiwyg/';

    /**
     * @return list<array{
     *     name: string,
     *     image: string,
     *     see_all_path: string,
     *     links: list<array{label: string, path: string}>
     * }>
     */
    public function getCategories(): array
    {
        return [
            [
                'name' => 'Hard Drive',
                'image' => self::MEDIA_BASE . 'tc-hdrive-wb.webp',
                'see_all_path' => 'storage-devices/internal-storage',
                'links' => [
                    ['label' => 'Solid State Drives', 'path' => 'storage-devices/internal-storage/solid-state-drives'],
                    ['label' => 'Desktop Storage', 'path' => 'storage-devices/internal-storage/desktop-hard-drive'],
                    ['label' => 'Server Storage', 'path' => 'storage-devices/internal-storage/server-hard-drive'],
                ],
            ],
            [
                'name' => 'CPUs',
                'image' => self::MEDIA_BASE . 'tc-cpus-wb.webp',
                'see_all_path' => 'cpus',
                'links' => [
                    ['label' => 'Intel Processors', 'path' => 'cpus/processors/intel-processors'],
                    ['label' => 'AMD Processors', 'path' => 'cpus/processors/amd-processors'],
                    ['label' => 'Processor Boards', 'path' => 'cpus/board/processors-boards'],
                ],
            ],
            [
                'name' => 'Memory',
                'image' => self::MEDIA_BASE . 'tc-ram-wb.webp',
                'see_all_path' => 'memory',
                'links' => [
                    ['label' => 'Server Memory', 'path' => 'memory/memory-classification/server-memory'],
                    ['label' => 'Desktop Memory', 'path' => 'memory/memory-classification/desktop-memory'],
                    ['label' => 'Laptop Memory', 'path' => 'memory/memory-classification/laptop-memory'],
                ],
            ],
            [
                'name' => 'Motherboard',
                'image' => self::MEDIA_BASE . 'tc-motherboard-wb.webp',
                'see_all_path' => 'motherboards',
                'links' => [
                    ['label' => 'Server Motherboards', 'path' => 'motherboards/motherboards-classification/server-motherboards'],
                    ['label' => 'Desktop Motherboards', 'path' => 'motherboards/motherboards-classification/desktop-motherboards'],
                    ['label' => 'Laptop Motherboards', 'path' => 'motherboards/motherboards-classification/laptop-motherboards'],
                ],
            ],
            [
                'name' => 'Power Supply',
                'image' => self::MEDIA_BASE . 'tc-power-wb.webp',
                'see_all_path' => 'power/power-classification',
                'links' => [
                    ['label' => 'Power Supplies', 'path' => 'power/power-classification/power-supplies'],
                    ['label' => 'UPS', 'path' => 'power/power-classification/ups'],
                    ['label' => 'Power Distribution Unit (PDU)', 'path' => 'power/power-classification/power-distribution-unit-pdu'],
                ],
            ],
            [
                'name' => 'SSD',
                'image' => self::MEDIA_BASE . 'tc-ssd-wb.webp',
                'see_all_path' => 'storage-devices/internal-storage/solid-state-drives',
                'links' => [
                    ['label' => 'Solid State Drives', 'path' => 'storage-devices/internal-storage/solid-state-drives'],
                    ['label' => 'Desktop Storage', 'path' => 'storage-devices/internal-storage/desktop-hard-drive'],
                    ['label' => 'Server Storage', 'path' => 'storage-devices/internal-storage/server-hard-drive'],
                ],
            ],
            [
                'name' => 'Networking',
                'image' => self::MEDIA_BASE . 'tc-networking-wb.webp',
                'see_all_path' => 'networking-devices',
                'links' => [
                    ['label' => 'Network Products', 'path' => 'networking-devices/network-products'],
                    ['label' => 'Switches', 'path' => 'networking-devices/switches'],
                    ['label' => 'Wireless Products', 'path' => 'networking-devices/wireless-products'],
                ],
            ],
            [
                'name' => 'Printers',
                'image' => self::MEDIA_BASE . 'tc-printer-wb.webp',
                'see_all_path' => 'printers-scanners/printers',
                'links' => [
                    ['label' => 'Laser Printers', 'path' => 'printers-scanners/printers/laser-printers'],
                    ['label' => 'InkJet Printers', 'path' => 'printers-scanners/printers/inkjet-printers'],
                    ['label' => 'Multifunction Printers', 'path' => 'printers-scanners/printers/multifunction-printers'],
                ],
            ],
        ];
    }
}
