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

## Configuration
No configuration is needed, but if you'd like - you can override the default configuration options here in your app/config/config.yml file as below:
```yml
afrihost_base_command:
    log_file_extention: '.log.txt'
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

## TODO
The following are features we are planning on adding:

- **Strategies for Logfile Names**: Currently the logfile name can either be specified manually or will be generated from
 the name of the file in which the commend is defined. We would like to make other options available via a Strategy Pattern 
- **Unhandled Exception Listener**: Have unhandled exceptions be automatically logged to the logger instantiated for the 
 command. This is already available in our production version. It just needs to be made more reusable
- **Bundle Config for**:
  - Default Log Level
  - Log to Console
  - PHP Error Reporting
- **User Specified LineFormatters**: Our default format (%datetime% \[%level_name%\]: %message%) is hardcoded. This isn't
 ideal if you wish to parse the logs with a specific tool.
- **Config for Monolog's AllowLineBreaks Option**: because sometimes you want a new line in the middle of a log entry
