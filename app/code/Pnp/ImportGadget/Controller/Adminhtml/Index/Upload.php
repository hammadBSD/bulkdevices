<?php

namespace Pnp\ImportGadget\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Backend\App\Action
{
    /**
     * FileSystem
     *
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * UploaderFactory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * UploaderFactory
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_io;

    public function __construct(
        Context $context,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $io
    )
    {
        $this->_uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->_io = $io;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $files = $this->getRequest()->getFiles();
        $field = 'import_images';
        if(!isset($files[$field]) || count($files[$field]) == 0) {
            $this->messageManager->addError("At least one image is required");
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $dirName = $this->getRequest()->getPost('directory_name');
        if(empty($dirName)) {
            $this->messageManager->addError("'Directory Name' is a required field.");
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $varDirectory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
        $path = $varDirectory->getAbsolutePath('import/images/' . $dirName);
        try {
            $this->_io->mkdir($path, 0755);
            foreach ($files[$field] as $index => $file) {
                $uploader = $this->_uploaderFactory->create(['fileId' => $field . "[$index]"]);
                $uploader->setAllowedExtensions(['jpg','jpeg','gif','png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $_FILES[$field]['name'][$index] = str_replace(' ', '-', $file['name']);
                $uploader->save($path, $_FILES[$field]['name'][$index]);
            }

            $this->messageManager->addSuccess("Files successfully uploaded");
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
	}
}

