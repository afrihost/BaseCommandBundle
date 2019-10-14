# Afrihost BaseCommandBundle
[![Latest Stable Version](https://poser.pugx.org/afrihost/base-command-bundle/v/stable)](https://packagist.org/packages/afrihost/base-command-bundle)
[![Total Downloads](https://poser.pugx.org/afrihost/base-command-bundle/downloads)](https://packagist.org/packages/afrihost/base-command-bundle)
[![License](https://poser.pugx.org/afrihost/base-command-bundle/license)](https://packagist.org/packages/afrihost/base-command-bundle)
[![Build Status](https://travis-ci.org/afrihost/BaseCommandBundle.svg?branch=master)](https://travis-ci.org/afrihost/BaseCommandBundle)

**If you have lots of Symfony Commands, or if you simply want to skip the boilerplate involved in setting up commands, this bundle is for you.** At its core is an abstract class that extends Symfony’s ContainerAwareCommand. This adds our own opinionated initialization for a range of boilerplate, such as logging and locking, so that you don’t have to re-invent the wheel with every command.

The overall design goal is to enable you to define defaults (such as whether to duplicate log output to your console) globally in your Symphony configuration while still having the freedom to override these in a single command (e.g. This command must always obtain a lock) and then change your mind again at runtime (to set the Log Level to DEBUG for this execution for example).

It is a small piece of ‘developer friendly’ code that we want to share with you in the hopes that it makes your life a little easier. If this appeals to you, Pull Requests are always welcome.

## Installation

First install this bundle using composer
```shell
composer require afrihost/base-command-bundle
```

> **Note:** Support for Symfony 2 and PHP 5 is still available under version zero (*~0.6*). We will continue to publish any
important bug and security fixes as point releases on this version, however, new features will not be backported. The current
version supports Symfony *~3.0* and PHP *~7.0*

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:
```php
// in AppKernel::registerBundles()
$bundles = array(
    // there should be a bunch of symfony bundles and your bundles already added here
    new Afrihost\BaseCommandBundle\AfrihostBaseCommandBundle(),
    // ...
);
```

## Configuration
Defaults are specified for all options so that no configuration is needed, but if you'd like, you can override the default configuration options in your `app/config/config.yml` file:
```yml
afrihost_base_command:
    locking:
        lock_file_folder:     storage
        enabled:              true
    logger:
        handler_strategies:
            file_stream:
                enabled:              true
                line_format:          '%%datetime%% [%%level_name%%]: %%message%%'
                file_extension:       .log.txt
            console_stream:
                enabled:              true
                line_format:          '%%datetime%% [%%level_name%%]: %%message%%'
```

**Locking:**
You may opt to enable/disable locking via configuration. Locking is enabled by default.

You might also want to change the default location where the lock files are created. If you do not specify a lock file
folder, locks will be created in the system's default temporary directory. If you specify a relative directory, locks will
be created relative to the Symfony Kernel root (which is "app" in most cases).  If you specify an absolute path, the directory
must already exist and be accessible by the PHP process. In POSIX environments, paths can be provided that start with ~/. 
This will be expanded using the $HOME environment variable and the full path subjected to he same constraints as absolute paths.

Example (relative): "storage" >> this will assume you want it under app/storage. "storage/lockfiles" >> this will assume you want it under "app/storage/lockfiles".
Example (absolute): "/var/my-lockfiles" >> this will store it under "/var/my-lockfiles". 
"~/my-lockfiles" >> lock files will be created under "/home/your_username/my-lockfiles".

**Logging**
The logging system has the ability to use several handlers. More handlers will be added soon, you're also welcome to add your own and send us a PR.
Each handler has the potential of being enabled/disabled. Handlers are all enabled by default at this stage.
Handlers generally have line formatting. The `line_format` entry states how this format looks like, and can be overwritten in the config.yml file.
The file logger has a file extension, specified by `file_extension` and is defaulted, but can again be overwritten in config.yml.

## Basic Usage
Instead of extending `ContainerAwareCommand` like this:
```php
class MyCoolCommand extends ContainerAwareCommand 
{
    // your stuff here
}
```
... you simply extend our `BaseCommand` like this:
```php
use Afrihost\BaseCommandBundle\Command\BaseCommand;

// ...

class MyCoolCommand extends BaseCommand
{
    // your stuff here
}
```

Don't worry, BaseCommand still extends ContainerAwareCommand, so all the goodies you are used to having at your disposal from ContainerAwareCommand are still there. BaseCommand merely adds a few extra boilerplate and tools for you to use, such as:

**Log Handler Initialisation** - you can immediately start logging out of the box with [Monolog](https://github.com/Seldaek/monolog):  
```PHP 
 $this->getLogger()->error('Hello World!')
```
**Runtime Log Level** - Change the log-level from the command line without having to change the code. Just provide the `--log-level` parameter with any of the RFC 5424 [severity names](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels) supported by Monolog:
```SHELL
$ php app/console my:cool:command --log-level=DEBUG
```
**Log to Console** - Toggle whether you want the log entries to be sent to STDOUT as well as the logfile

## Output Icons
```PHP 
 echo $this->getIcon()->tick()->white()->bgGreen()->bold()->render() . PHP_EOL;
```

<table>
<tr><th>Icons</th><th>Colours</th><th>Options</th></tr>
<tr><td valign="top">
<table>
<tr><th>Icon</th><th>Method</th></tr>
<tr><td>&#x2714;</td><td>tick</td></tr>
<tr><td>&#x2718;</td><td>error</td></tr>
<tr><td>&#x2757;</td><td>exclamation</td></tr>
<tr><td>&#x2770;</td><td>lt</td></tr>
<tr><td>&#x2771;</td><td>gt</td></tr>
<tr><td>&#x2780;</td><td>one</td></tr>
<tr><td>&#x2781;</td><td>two</td></tr>
<tr><td>&#x2782;</td><td>three</td></tr>
<tr><td>&#x2783;</td><td>four</td></tr>
<tr><td>&#x2784;</td><td>five</td></tr>
<tr><td>&#x2785;</td><td>six</td></tr>
<tr><td>&#x2786;</td><td>seven</td></tr>
<tr><td>&#x2787;</td><td>eight</td></tr>
<tr><td>&#x2788;</td><td>nine</td></tr>
<tr><td>&#x2789;</td><td>ten</td></tr>
<tr><td>&#x2709;</td><td>envelope</td></tr>
<tr><td>&#x2620;</td><td>dead</td></tr>
<tr><td>&#x26D4;</td><td>noEntry</td></tr>
<tr><td>&#x23F0;</td><td>alarmClock</td></tr>
<tr><td>&#x2190;</td><td>leftArrow</td></tr>
<tr><td>&#x2191;</td><td>upArrow</td></tr>
<tr><td>&#x2192;</td><td>rightArrow</td></tr>
<tr><td>&#x2193;</td><td>downArrow</td></tr>
<tr><td>&#x2194;</td><td>leftRightArrow</td></tr>
<tr><td>&#x2195;</td><td>upDownArrow</td></tr>
<tr><td>&#x1f4a9;</td><td>smileyPoo</td></tr>
<tr><td>&#x1f37b;</td><td>beers</td></tr>
<tr><td>&#x1f414;</td><td>chicken</td></tr>
<tr><td>&#x1f4a3;</td><td>bomb</td></tr>
<tr><td>&#x1f4a4;</td><td>snooze</td></tr>
<tr><td>&#x1f512;</td><td>lock</td></tr>
<tr><td>&#128591;</td><td>pray</td></tr>
</table>
</td>
<td valign="top">
<table>
<tr><th>Foreground Colour Methods</th></tr>
<tr><td>default</td></tr>
<tr><td>black</td></tr>
<tr><td>white</td></tr>
<tr><td>red</td></tr>
<tr><td>green</td></tr>
<tr><td>blue</td></tr>
<tr><td>yellow</td></tr>
<tr><td>cyan</td></tr>
<tr><td>magenta</td></tr>
</table>

<table>
<tr><th>Background Colour Methods</th></tr>
<tr><td>bgDefault</td></tr>
<tr><td>bgBlack</td></tr>
<tr><td>bgWhite</td></tr>
<tr><td>bgRed</td></tr>
<tr><td>bgGreen</td></tr>
<tr><td>bgBlue</td></tr>
<tr><td>bgYellow</td></tr>
<tr><td>bgCyan</td></tr>
<tr><td>bgMagenta</td></tr>
</table>

</td>
<td valign="top">
<table>
<tr><th>Method</th></tr>
<tr><td>bold</td></tr>
<tr><td>underscore</td></tr>
<tr><td>reverse</td></tr>
</table>
</td>
</tr>
</table>

## TODO
The following are features we would like to add. When this list is done (or reasonably short) we will release our first Major Version:

- [ ] **Strategies for Logfile Names**: Currently the logfile name can either be specified manually or will be generated from
 the name of the file in which the commend is defined. We would like to make other options available via a Strategy Pattern 
 (Log filename can be specified in a parameter as an interim solution)
- [x] **Configurable Logfile Extension**: For historical reasons logfile names all end in `.log.txt`. This extension should be a configuration option
- [ ] **Unhandled Exception Listener**: Have unhandled exceptions be automatically logged to the logger instantiated for the 
 command. This is already available in our production version. It just needs to be made more reusable
- [ ] **Bundle Config for**:
  - [ ] Default Log Level
  - [x] Log to Console
  - [x] Log to File
  - [ ] PHP Error Reporting
  - [x] PHP memory_limit
  - [ ] PHP maximum execution time
  - [x] Specify lock-handler lockfile location
- [x] **User Specified LineFormatters**: Our default format (%datetime% \[%level_name%\]: %message%) is hardcoded. This isn't
 ideal if you wish to parse the logs with a specific tool.
- [x] **Locking**: Integrate mechanism to ensure that only one process is executing a command at a time 
- [x] **Config for Monolog's AllowLineBreaks Option**: because sometimes you want a new line in the middle of a log entry
- [x] **PHPUnit**: config and basic code coverage. The goal is to have some form of Github integrated CI
- [x] **Output Icons**: create a helper to prefix output with unicode icons (such as a checkmark)
- [ ] **Documentation**:
  - [x] ~~Changelog~~ (listing major changes on Github releases)
  - [ ] Seed `Resources/doc/` ( [Symfony Best Practice](http://symfony.com/doc/current/cookbook/bundles/best_practices.html#directory-structure) )
  - [ ] Contributor Guide

