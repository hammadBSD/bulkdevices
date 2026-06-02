<?php

namespace BSD\GoHighLevel\Model;

use Magento\Framework\ObjectManagerInterface;

class GhlContactFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create model instance
     *
     * @param array $data
     * @return GhlContact
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(GhlContact::class, $data);
    }
}
