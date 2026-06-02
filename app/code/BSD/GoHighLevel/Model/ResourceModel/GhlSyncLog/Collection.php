<?php

namespace BSD\GoHighLevel\Model\ResourceModel\GhlSyncLog;

use BSD\GoHighLevel\Model\GhlSyncLog;
use BSD\GoHighLevel\Model\ResourceModel\GhlSyncLog as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(GhlSyncLog::class, ResourceModel::class);
    }
}
