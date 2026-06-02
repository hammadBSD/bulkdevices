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
namespace BSD\Banner\Block\Adminhtml;

/**
 * Class Banner
 *
 * @category BSD
 * @package  BSD_Banner
 * @author   BSD <magento@bsd-technologies.com>
 * @license  https://www.bsd-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.bsd-technologies.com
 */
class Banner extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_blockGroup = 'BSD_Banner';
        $this->_controller = 'adminhtml';
        $this->_headerText = __('Banner');
        $this->_addButtonLabel = __('Add New Banner');
        parent::_construct();
        if (!$this->_authorization->isAllowed('BSD_Banner::add_banner')) {
            $this->removeButton('add');
        }
    }
}
