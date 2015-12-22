# Afrihost BaseCommandBundle

## Installation
`composer require afrihost/base-command-bundle`

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
