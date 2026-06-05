<?php

namespace BSD\GoHighLevel\Model;

use BSD\GoHighLevel\Model\ResourceModel\GhlOpportunity as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class GhlOpportunity extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
