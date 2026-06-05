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
namespace BSD\Banner\Controller\Adminhtml;

/**
 * Class Banner
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Banner extends Actions
{
    /**
     * Form session key
     *
     * @var string
     */
    protected $formSessionKey = 'bsd_banner_form_data';

    /**
     * Allowed Key
     *
     * @var string
     */
    protected $allowedKey = 'BSD_Banner::manage_banners';

    /**
     * Model class name
     *
     * @var string
     */
    protected $modelClass = \BSD\Banner\Model\Banner::class;

    /**
     * Active menu key
     *
     * @var string
     */
    protected $activeMenu = 'BSD_Banner::banner';

    /**
     * Status field name
     *
     * @var string
     */
    protected $statusField = 'is_active';

    /**
     * Save request params key
     *
     * @var string
     */
    protected $paramsHolder = 'post';
}
