<?php
namespace Pnp\RunningCronJobs\Model\ResourceModel;

class Cronschedule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cron_schedule', 'schedule_id');
    }
}
?>