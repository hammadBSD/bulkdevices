<?php

namespace Pnp\ImportGadget\Block\Adminhtml\Index;

use Psr\Log\LoggerInterface;

class Index extends \Magento\Backend\Block\Widget\Container
{
    protected $_driverFile;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        array $data = []
    )
    {
        $this->_driverFile = $driverFile;
        parent::__construct($context, $data);
    }


    public function getAllFiles() {
        $path = $this->getVarDirectory()->getAbsolutePath('import/images');
        $paths = [];
        try {
            //read just that single directory
            $paths =  $this->_driverFile->readDirectory($path);
        } catch (FileSystemException $e) {
            $this->_logger->error($e->getMessage());
        }

        return $paths;
    }

    private function getVarDirectory() {
        return $this->_filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}
