<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OneStepCheckout
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OneStepCheckout\Model;

class ConvertFileName
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;

    /**
     * @var \Magento\Framework\Module\Dir
     */
    private $directory;

    /**
     * @var \Bss\OneStepCheckout\Helper\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Framework\Module\Dir $directory
     * @param \Bss\OneStepCheckout\Helper\Config $config
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Module\Manager $manager,
        \Magento\Framework\Module\Dir $directory,
        \Bss\OneStepCheckout\Helper\Config $config
    ){
        $this->file = $file;
        $this->manager = $manager;
        $this->directory = $directory;
        $this->config = $config;
    }

    /**
     * Convert file gift wrapper
     *
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function Convert()
    {
        $fileTxt = "/Model/Checkout/Orderlines/GiftWrapper.txt";
        $filePhp = "/Model/Checkout/Orderlines/GiftWrapper.php";
        $fileTxtV244 = "/Model/Checkout/Orderlines/GiftWrapperV244.txt";
        $filePhpV244 = "/Model/Checkout/Orderlines/GiftWrapperV244.php";
        $directoryPath = $this->directory->getDir(
            'Bss_OneStepCheckout'
        );
        if ($this->config->isLessThanM244()) {
            if ($this->manager->isEnabled('Klarna_Core')) {
                if ($this->file->isExists($directoryPath.$fileTxt)) {
                    try {
                        $this->file->rename($directoryPath.$fileTxt, $directoryPath.$filePhp);
                        $message = __("Convert file successful.");
                    } catch (\Exception $e) {
                        $message = __("Permission denied.");
                    }
                } elseif ($this->file->isExists($directoryPath.$filePhp)) {
                    $message = __("Convert file successful.");
                } else {
                    $message = __("Can not find the file.");
                }
            } else {
                if ($this->file->isExists($directoryPath.$filePhp)) {
                    try {
                        $this->file->rename($directoryPath.$filePhp, $directoryPath.$fileTxt);
                        $message = __("Convert file successful.");
                    } catch (\Exception $e) {
                        $message = __("Permission denied.");
                    }
                }elseif ($this->file->isExists($directoryPath.$fileTxt)) {
                    $message = __("Convert file successful.");
                } else {
                    $message = __("Can not find the file.");
                }
            }
        } else {
            if ($this->config->isGiftWrapEnable()) {
                if ($this->file->isExists($directoryPath.$fileTxtV244)) {
                    try {
                        $this->file->rename($directoryPath.$fileTxtV244, $directoryPath.$filePhpV244);
                        $message = __("Convert file successful.");
                    } catch (\Exception $e) {
                        $message = __("Permission denied.");
                    }
                } elseif ($this->file->isExists($directoryPath.$filePhpV244)) {
                    $message = __("Convert file successful.");
                } else {
                    $message = __("Can not find the file.");
                }
            } else {
                if ($this->file->isExists($directoryPath.$filePhpV244)) {
                    try {
                        $this->file->rename($directoryPath.$filePhpV244, $directoryPath.$fileTxtV244);
                        $message = __("Convert file successful.");
                    } catch (\Exception $e) {
                        $message = __("Permission denied.");
                    }
                }elseif ($this->file->isExists($directoryPath.$fileTxtV244)) {
                    $message = __("Convert file successful.");
                } else {
                    $message = __("Can not find the file.");
                }
            }
        }
        return $message;
    }
}
