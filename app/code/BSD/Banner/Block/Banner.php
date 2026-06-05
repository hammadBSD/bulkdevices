<?php
/**
 * Class Banner
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */

namespace BSD\Banner\Block;

use Magento\Framework\Stdlib\DateTime\DateTime;
use BSD\Banner\Api\Data\BannerInterface;
use BSD\Banner\Model\ResourceModel\Banner\Collection as BannerCollection;

/**
 * Class Banner
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Banner extends \Magento\Framework\View\Element\Template implements
    \Magento\Framework\DataObject\IdentityInterface
{

    /**
     * StoreManager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * DataCollection
     *
     * @var \Magento\Framework\Data\Collection
     */
    protected $dataCollection;

    /**
     * BannerCollectionFactory
     *
     * @var \BSD\Banner\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * Timezone
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;
    /**
     * @var Session
     */
    private $customerSesion;

    /**
     * DateTime
     *
     * @var DateTime
     */
    protected $date;

    /**
     * BannerHelper
     *
     * @var \BSD\Banner\Helper\Data
     */
    protected $bannerHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Banner constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \BSD\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Magento\Framework\Data\Collection $dataCollection
     * @param \BSD\Banner\Helper\Data $bannerHelper
     * @param \BSD\Banner\Model\ResourceModel\Banner $resourceBanner
     * @param \Magento\Customer\Model\Session $customerSesion
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \BSD\Banner\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory,
        \Magento\Framework\Data\Collection $dataCollection,
        \BSD\Banner\Helper\Data $bannerHelper,
        \BSD\Banner\Model\ResourceModel\Banner $resourceBanner,
        \Magento\Customer\Model\Session $customerSesion,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->storeManager = $context->getStoreManager();
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->dataCollection = $dataCollection;
        $this->customerSesion = $customerSesion;
        $this->date = $date;
        $this->bannerHelper = $bannerHelper;
        $this->resourceBanner = $resourceBanner;
        $this->_filterProvider = $filterProvider;
    }

    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getConfig($config)
    {
        return $this->bannerHelper->getConfig($config);
    }

    /**
     * Return Banner Collection
     *
     * @return \BSD\Banner\Model\ResourceModel\Banner\Collection
     * @throws \Exception
     */
    public function getBanner()
    {
        if (!$this->hasData('banner')) {
            $date = $this->timezoneInterface->date()->format('Y-m-d H:i:s');
            $bannerCollection = $this->bannerCollectionFactory->create()->addFilter('is_active', 1)
                ->addFieldToFilter('store', $this->storeManager->getStore()->getId())
                ->addFieldToFilter('customer', $this->customerSesion->getCustomerGroupId());
            $bannerCollection->getSelect()->group('banner_id');
            $bannerCollection->getSelect()->order(BannerInterface::POSITION, BannerCollection::SORT_ORDER_ASC);
            $collection = $this->dataCollection;

            foreach ($bannerCollection as $banner) {
                $data = $banner->getData();

                if ((($data['start_date'] <= $date) || ($data['start_date'] == null) || ($data['start_date'] == "0000-00-00 00:00:00"))
                    && (($data['end_date'] >= $date) || ($data['end_date'] == null) || ($data['end_date'] == "0000-00-00 00:00:00"))
                ) {
                    $rowObj = new \Magento\Framework\DataObject();
                    $rowObj->setData($data);
                    $collection->addItem($rowObj);
                }
            }
            $this->setData('banner', $collection);
        }

        return $this->getData('banner');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\BSD\Banner\Model\Banner::CACHE_TAG . '_' . 'list'];
    }

    /**
     * Return Media Path
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaPath()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function getCmsFilterContent($value='')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
}
