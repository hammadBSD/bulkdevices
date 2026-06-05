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
namespace Bss\OneStepCheckout\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenameGiftWrapper extends Command
{
    /**
     * @var \Bss\OneStepCheckout\Model\ConvertFileName
     */
    private $convertGiftWrapper;

    /**
     * @param \Bss\OneStepCheckout\Model\ConvertFileName $convertGiftWrapper
     */
    public function __construct(
        \Bss\OneStepCheckout\Model\ConvertFileName $convertGiftWrapper
    ){
        parent::__construct();
        $this->convertGiftWrapper = $convertGiftWrapper;
    }

    /**
     * Create cli
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bss_o_s_c:convertfile');
        $this->setDescription('Convert file name.');
        parent::configure();
    }

    /**
     * Rename file with cli
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->convertGiftWrapper->Convert();
        if ($message !== null) {
            $output->writeln($message);
        }
    }
}
