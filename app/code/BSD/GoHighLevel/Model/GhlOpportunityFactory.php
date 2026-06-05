<?php

namespace BSD\GoHighLevel\Model;

use Magento\Framework\ObjectManagerInterface;

class GhlOpportunityFactory
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
     * @return GhlOpportunity
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(GhlOpportunity::class, $data);
    }
}
