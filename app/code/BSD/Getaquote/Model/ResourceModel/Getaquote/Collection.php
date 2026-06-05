<?php

namespace BSD\Getaquote\Model\ResourceModel\Getaquote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BSD\Getaquote\Model\Getaquote', 'BSD\Getaquote\Model\ResourceModel\Getaquote');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>