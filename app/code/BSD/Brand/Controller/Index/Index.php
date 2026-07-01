<?php

declare(strict_types=1);

namespace BSD\Brand\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

class Index implements HttpGetActionInterface
{
    public function execute(): ResultInterface
    {
        throw new NotFoundException(__('Page not found.'));
    }
}
