<?php

declare(strict_types=1);

namespace BSD\Brand\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\Action\Forward;

class Router implements RouterInterface
{
    public function __construct(
        private readonly ActionFactory $actionFactory,
    ) {
    }

    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');
        if ($identifier === '' || !str_starts_with($identifier, 'brand')) {
            return null;
        }

        $parts = explode('/', $identifier);
        if ($parts[0] !== 'brand') {
            return null;
        }

        if (count($parts) === 2 && $parts[1] !== '') {
            $request->setModuleName('brand')
                ->setControllerName('view')
                ->setActionName('index')
                ->setParam('slug', $parts[1]);

            return $this->actionFactory->create(Forward::class);
        }

        return null;
    }
}
