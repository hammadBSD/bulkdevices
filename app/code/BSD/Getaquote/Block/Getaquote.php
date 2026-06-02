<?php

namespace BSD\Getaquote\Block;

use Magento\Framework\View\Element\Template;

class Getaquote extends Template
{
    public function getRecaptchaSiteKey()
    {
        return $this->getConfigValue(\BSD\Getaquote\Controller\Index\Save::XML_PATH_RECAPTCHA_SITE_KEY);
    }

    public function getRecaptchaSecretKey()
    {
        return $this->getConfigValue(\BSD\Getaquote\Controller\Index\Save::XML_PATH_RECAPTCHA_SECRET_KEY);
    }

    public function getRecaptchaScriptUrl()
    {
        return $this->getConfigValue(\BSD\Getaquote\Controller\Index\Save::XML_PATH_RECAPTCHA_SCRIPT_URL);
    }

    private function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
