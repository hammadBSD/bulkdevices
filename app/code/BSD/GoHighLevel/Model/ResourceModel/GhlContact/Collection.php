<?php

namespace BSD\GoHighLevel\Model\ResourceModel\GhlContact;

use BSD\GoHighLevel\Model\GhlContact;
use BSD\GoHighLevel\Model\ResourceModel\GhlContact as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(GhlContact::class, ResourceModel::class);
    }
}
