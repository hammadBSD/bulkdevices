<?php

namespace BSD\GoHighLevel\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class GhlSyncLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bsd_ghl_sync_log', 'id');
    }
}
