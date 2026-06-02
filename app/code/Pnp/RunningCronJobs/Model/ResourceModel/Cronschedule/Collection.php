<?php

namespace Pnp\RunningCronJobs\Model\ResourceModel\Cronschedule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Pnp\RunningCronJobs\Model\Cronschedule', 'Pnp\RunningCronJobs\Model\ResourceModel\Cronschedule');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>