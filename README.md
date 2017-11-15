# magento2-set-thumbnails
Magento 2 CLI tool for setting the first image on a product to the thumbnail.
Offers the option to replace all thumbnail images or to leave those that are already set.

## Installation ##
Install using composer
```composer require creode/magento2-set-thumbnails```

### Development Usage ###
You don't need to run the maintenance or static content deploy commands if you are in development mode.

```bin/magento creode:set-thumbnails```


### Live Usage ###
```bin/magento maintenance:enable```
```bin/magento creode:set-thumbnails```
```bin/magento setup:static-content:deploy```
```bin/magento maintenance:disable```