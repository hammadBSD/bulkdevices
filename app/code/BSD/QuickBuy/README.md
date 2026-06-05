# Magento 2 QuickBuy 

This module add "QuickBuy" button on product view page and list page to process directly checkout.

QuickBuy Configuration: Stores->Configuration->MagePrince->Quick Buy

# Notice

<b>We don't support Quick Buy button on related, upsell, wishlist or any other places because it needs override core phtml files which is not the recommended solution. Please keep in note that most of the paid or free version of the Quick Buy module overrides the core phtml files.</b>

# Copy below code to add Quick Buy button in custom product sliders, widget, static blocks etc.

``````
$buyNowHtml = $this->getLayout()
    ->createBlock('BSD\QuickBuy\Block\Product\ListProduct')
    ->setProduct($_item)
    ->setTemplate('BSD_QuickBuy::quickbuy-list.phtml')
    ->toHtml();
echo $buyNowHtml;
``````
<b>Change `$_item` to current product object.</b>

You can use above code where you want to show buy now button in product. Please make sure don't copy this code to addtocart or any other form. Put this code after any `</form>`. Here is the screenshot of sample code of usage


# Installation Instruction

* Copy the content of the repo to the <b>app/code/BSD/QuickBuy</b> folder
* Run command:
<b>php bin/magento setup:upgrade</b>
* Run Command:
<b>php bin/magento setup:static-content:deploy</b>
* Now Flush Cache: <b>php bin/magento cache:flush</b>


# How To Find Addtocart Form Id - Useful For Custom Theme

Go to product view page and right click on addtocart button and click on inspect element. Then scroll up and find addtocart form id.
