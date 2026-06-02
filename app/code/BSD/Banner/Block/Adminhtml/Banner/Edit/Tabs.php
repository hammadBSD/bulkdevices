<?php
/**
 * Class Tabs
 *
 * PHP version 7
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
namespace BSD\Banner\Block\Adminhtml\Banner\Edit;

/**
 * Class Tabs
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('Banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Banner Information'));
    }
}
