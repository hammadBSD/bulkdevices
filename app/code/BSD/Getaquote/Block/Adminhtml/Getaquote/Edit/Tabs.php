<?php
namespace BSD\Getaquote\Block\Adminhtml\Getaquote\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('getaquote_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Getaquote Information'));
    }
}