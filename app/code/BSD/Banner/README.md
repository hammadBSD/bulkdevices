Banner Extension for Magneto 2 developed by BSD

Welcome to Banner Slider Extension for Magneto 2 developed by BSD.

The extension lets the store owners place smart banners on their website and also helps in creating unlimited images and video banners for your website to reach the target audience.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the files
3. Create a folder [Magento_Root]/app/code/BSD/Banner
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/BSD/Banner'

#Enable Extension:
- php bin/magento module:enable BSD_Banner
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable BSD_Banner
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

{{block class="\Sparsh\Banner\Block\Banner" template="Sparsh_Banner::banner.phtml"}}