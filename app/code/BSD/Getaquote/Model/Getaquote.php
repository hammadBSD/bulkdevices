<?php
namespace BSD\Getaquote\Model;

class Getaquote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BSD\Getaquote\Model\ResourceModel\Getaquote');
    }
}
?>