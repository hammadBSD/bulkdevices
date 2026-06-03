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
        $placeholders = [];
        $min = preg_replace_callback(
            '/<(script|style)\b[^>]*>.*?<\/\1>/is',
            static function (array $matches) use (&$placeholders): string {
                $key = '%%HTMLMIN' . count($placeholders) . '%%';
                $placeholders[$key] = $matches[0];
                return $key;
            },
            $body
        );

        $min = preg_replace('/<!--(?!\s*\[if).*?-->/s', '', (string) $min);
        $min = preg_replace('/\s{2,}/', ' ', (string) $min);
        $min = preg_replace('/>\s+</', '><', (string) $min);

        if ($placeholders !== []) {
            $min = strtr((string) $min, $placeholders);
        }

        $subject->setBody(trim((string) $min));
    }
}
