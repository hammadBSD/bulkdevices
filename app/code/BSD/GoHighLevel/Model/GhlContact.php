<?php

namespace BSD\GoHighLevel\Model;

use BSD\GoHighLevel\Model\ResourceModel\GhlContact as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class GhlContact extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
