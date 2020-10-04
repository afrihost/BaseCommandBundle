# Contributing To This Repo
If you are New to Github, This is a guideline how to contribute [Setting Guidelines](https://help.github.com/articles/setting-guidelines-for-repository-contributors/)

## Installation 
Install this bundle with composer
```shell
composer require afrihost/base-command-bundle
```
Next, enable the bundle by adding it to the list of registered bundles in the app/AppKernel.php file of your project:
```php
// in AppKernel::registerBundles()
$bundles = array(
    // there should be a bunch of symfony bundles and your bundles already added here
    new Afrihost\BaseCommandBundle\AfrihostBaseCommandBundle(),
    // ...
);
```

## Futher Instruction

For Futher Insctruction about this package visit [ReadMe](https://github.com/afrihost/BaseCommandBundle/blob/master/README.md)
