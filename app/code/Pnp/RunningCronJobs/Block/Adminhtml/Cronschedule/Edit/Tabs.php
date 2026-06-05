<?php
namespace Pnp\RunningCronJobs\Block\Adminhtml\Cronschedule\Edit;

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
        $this->setId('cronschedule_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Cronschedule Information'));
    }
}