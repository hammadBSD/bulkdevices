<?php

/**
 * MagePrince
 * Copyright (C) 2020 BSD <info@quickbuy.com>
 *
 * @package BSD_QuickBuy
 * @copyright Copyright (c) 2020 BSD (http://www.quickbuy.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MagePrince <info@quickbuy.com>
 */

namespace BSD\QuickBuy\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Disable extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}
