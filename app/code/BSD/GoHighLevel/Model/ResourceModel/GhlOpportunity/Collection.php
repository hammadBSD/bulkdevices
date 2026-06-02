<?php

namespace BSD\GoHighLevel\Model\ResourceModel\GhlOpportunity;

use BSD\GoHighLevel\Model\GhlOpportunity;
use BSD\GoHighLevel\Model\ResourceModel\GhlOpportunity as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(GhlOpportunity::class, ResourceModel::class);
    }
}
