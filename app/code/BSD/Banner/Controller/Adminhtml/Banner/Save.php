<?php
/**
 * Class save
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
namespace BSD\Banner\Controller\Adminhtml\Banner;

use BSD\Banner\Model\Banner;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Class save
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Save extends \BSD\Banner\Controller\Adminhtml\Banner
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
    public $uploaderfactory;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context           $context         context
     * @param Filesystem                                    $fileSystem      fileSystem
     * @param UploaderFactory                               $uploaderfactory uploaderfactory
     * @param \BSD\Banner\Model\BannerFactory        $bannerFactory   bannerFactory
     * @param \BSD\Banner\Model\ResourceModel\Banner $bannerResource  bannerResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Filesystem $fileSystem,
        UploaderFactory $uploaderfactory,
        \BSD\Banner\Model\BannerFactory $bannerFactory,
        \BSD\Banner\Model\ResourceModel\Banner $bannerResource,
        \BSD\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
    ) {
        $this->filesystem = $fileSystem;
        $this->uploaderfactory = $uploaderfactory;
        parent::__construct($context, $bannerFactory, $bannerResource, $bannerCollectionFactory);
    }

    /**
     * Before save method
     *
     * @param \BSD\Banner\Model\Banner         $model   model
     * @param \Magento\Framework\App\RequestInterface $request request
     *
     * @return bool|void
     */
    protected function _beforeSave($model, $request)
    {
        $data = $model->getData();
        $model->setData($data);
        $mediaDirectory = $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        );

        if ($data['banner_type'] == "Image") {
            $imageField = 'banner_image';
            /* prepare banner image */
            if (isset($data[$imageField]) && isset($data[$imageField]['value'])) {
                if (isset($data[$imageField]['delete'])) {
                    unlink($mediaDirectory->getAbsolutePath() . $data[$imageField]['value']);
                    $model->setData($imageField, '');
                } else {
                    $model->setData($imageField, $data[$imageField]['value']);
                }
            }
            try {
                $uploader =$this->uploaderfactory;
                $uploader = $uploader->create(['fileId' => 'post['.$imageField.']']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save(
                    $mediaDirectory->getAbsolutePath(Banner::BASE_IMAGE_MEDIA_PATH)
                );
                $model->setData(
                    $imageField,
                    Banner::BASE_IMAGE_MEDIA_PATH . $result['file']
                );
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Please Insert Image of types jpg, jpeg, gif, png')
                    );
                }
            }
        } else {
            $model->setData('banner_image', '')
                ->setData('banner_title', '')
                ->setData('label_button_text', '')
                ->setData('call_to_action', '')
                ->setData('banner_description', '');
        }

        if ($data['banner_type'] == "Video") {

            $videoField = 'banner_video';
            try {
                $uploaderFactory = $this->uploaderfactory->create(['fileId' => 'post['.$videoField.']']);

                $uploaderFactory->setAllowedExtensions(['mp4']);
                if ($uploaderFactory->getFileExtension() != 'mp4') {
                    $this->messageManager->addErrorMessage(
                        __('only mp4 files allowed 2.')
                    );
                    return false;
                }
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);
                $uploaderFactory->setAllowCreateFolders(true);
                $destinationPath = $mediaDirectory->getAbsolutePath(Banner::BASE_VIDEO_MEDIA_PATH);
                $result = $uploaderFactory->save($destinationPath);
                $model->setData(
                    $videoField,
                    Banner::BASE_VIDEO_MEDIA_PATH . $result['file']
                );
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Please Insert Video of type mp4')
                    );
                }
            }
            $imageField = 'banner_video_thumb_image';
            /* prepare banner image */
            if (isset($data[$imageField]) && isset($data[$imageField]['value'])) {
                if (isset($data[$imageField]['delete'])) {
                    unlink($mediaDirectory->getAbsolutePath() . $data[$imageField]['value']);
                    $model->setData($imageField, '');
                } else {
                    $model->setData($imageField, $data[$imageField]['value']);
                }
            }
            try {
                $uploader =$this->uploaderfactory;
                $uploader = $uploader->create(['fileId' => 'post['.$imageField.']']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save(
                    $mediaDirectory->getAbsolutePath(Banner::BASE_IMAGE_MEDIA_PATH)
                );
                $model->setData(
                    $imageField,
                    Banner::BASE_IMAGE_MEDIA_PATH . $result['file']
                );
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Please Insert Image of types jpg, jpeg, gif, png')
                    );
                }
            }
        } else {
            $model->setData('banner_video', '');
            $model->setData('banner_video_thumb_image', '');
        }

        if ($data['banner_type'] != 'Youtube') {
            $model->setData('banner_youtube', '');
        }
    }
}
