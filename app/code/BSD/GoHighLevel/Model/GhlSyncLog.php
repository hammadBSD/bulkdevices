<?php

namespace BSD\GoHighLevel\Model;

use BSD\GoHighLevel\Model\ResourceModel\GhlSyncLog as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class GhlSyncLog extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
