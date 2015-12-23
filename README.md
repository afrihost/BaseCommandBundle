# Afrihost BaseCommandBundle

## Installation
`composer require afrihost/base-command-bundle`

You also have to activate the bundle in your AppKernel.php:
```php
    $bundles = array(
        // there should be a bunch of symfony bundles and your bundles already added here
        new \Afrihost\BaseCommandBundle\AfrihostBaseCommandBundle(),
    );
```

## Usage
Instead of declaring your Command like this:
```php
class MyCoolCommand extends ContainerAwareCommand 
{
    // your stuff here
}
```
... you would declare it this way:
```php
class MyCoolCommand extends BaseCommand
{
    // your stuff here
}
```

Don't worry: BaseCommand still extends ContainerAwareCommand, so all the goodies you are used to having at your disposal from ContainerAwareCommand is still there. BaseCommand merely adds a few extra boilerplate and tools for you to use, such as:

* Logger accessibility via $this: Easy access the logger, which has already been instantiated and set up for standard use
* CLI Logger option: Changing the log-level from the command line without having to change the code each time you want to change the level
* Log to console: Toggle whether you want the log to be sent to stdout as well as the logfile
