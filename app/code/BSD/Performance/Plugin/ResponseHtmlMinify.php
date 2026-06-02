<?php
declare(strict_types=1);

namespace BSD\Performance\Plugin;

use Magento\Framework\App\Response\Http;

class ResponseHtmlMinify
{
    public function beforeSendResponse(Http $subject): void
    {
        $body = (string) $subject->getBody();
        if ($body === '' || stripos($body, '<html') === false) {
            return;
        }
        $min = preg_replace('/<!--(?!\s*\[if).*?-->/s', '', $body);
        $min = preg_replace('/\s{2,}/', ' ', $min);
        $min = preg_replace('/>\s+</', '><', (string) $min);
        $subject->setBody(trim((string) $min));
    }
}
