<?php
namespace Pnp\RunningCronJobs\Model;

class Cronschedule extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Pnp\RunningCronJobs\Model\ResourceModel\Cronschedule');
    }
}
?>