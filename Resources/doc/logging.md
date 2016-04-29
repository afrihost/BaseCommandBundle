# Logging
A [Monolog](https://github.com/Seldaek/monolog) logger is configured for each command that extends the `BaseCommand` 
class. It can be accessed by calling `$this->getLogger()`, which returns an object that implements the PSR-3 
[LoggerInterface](https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php). You can use this in the same way that you would use the logger returned by Symfony's built in [logging service](http://symfony.com/doc/current/cookbook/logging/monolog.html). This logger is pre-configured 
with handlers to log to your terminal and to file. As with everything else in the bundle, this is fully customizable. 

## Why is this Useful? ##
For small projects, you could probably get away with just using Symfony's built in [logging 
service](http://symfony.com/doc/current/cookbook/logging/monolog.html), however as your project grows, having 
independently configurable loggers may become useful for the following reasons: 

 - Different commands may require different levels of logging verbosity depending on their maturity
 - You will probably want different commands to log to different places for both IO performance and easy of readability
 - A useful paradigm is to only use Symfony's logging service for log entries related to web requests in order to differentiate these 

## Example Usage ##
In the following example, log entries are written to record the beginning and end of the command's execution. These are logged at the *INFO* level of verbosity while the contents of any exceptions are logged at the *ERROR* level of verbosity. In the `initialize()` function the global default logging level of *WARNING* is overridden for this command so that that *INFO* log entries are outputted.
```PHP
use Monolog\Logger;

// ...

protected function initialize(InputInterface $input, OutputInterface $output)
{
    $this->setLogLevel(Logger::INFO); // Override default of WARNING
    parent::initialize($input, $output);
}

protected function execute(InputInterface $input, OutputInterface $output)
{
    $this->getLogger()->info('Starting Execution');

    try {
        // Do so stuff
    } catch(\Exception $e){
        $this->getLogger()->error('Exception Encountered: '.$e->getMessage());
    }

    $this->getLogger()->info('Done');
}
```
The idea is that once this code becomes stable, the `setLogLevel()` line can be removed so that only the exception messages are logged. The entries that are logged at *INFO* verbosity can be left in the code so that if the extra verbosity is ever needed again to debug, the entries can be accessed (per execution) using the [--log-level](#--log-level) parameter.

This is a simple example. There is a large variety of other logging functionality detailed in the [Function Reference](#function-reference) below.

### Global Config 
All the configuration options for the *Logging Enhancement* fall under the `logger` config node. As all fields 
have 'sensible' defaults, no configuration is required to start using the bundle. These options are available should you 
want to customise the logging functionality to your needs. The default values are shown below: 

```yaml
afrihost_base_command:
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
#### Handler Strategies
There are currently two Monolog LogHandlers that are automatically configured by default: 

1. File Stream 
2. Console Stream 

Both handlers will log at WARNING verbosity by default. This can be overridden per command using the 
[setLogLevel](#setloglevel-int-loglevel-) function or at runtime using the [--log-level](#--log-level) parameter. 
 
##### File Stream  
This handler will output log records to a log file. The file will be created in the default [Log 
Directory](http://symfony.com/doc/current/reference/configuration/kernel.html#log-directory) of the executing Symfony 
Kernel. Typically this is `app/logs/`. 
 
Unless explicitly specified in the implementation of the command, the default filename will be the same as the name of 
the PHP file in which the command class is implemented with `.log.txt` appended as an extension.

**For example:** if you have a command defined in `HelloWorldCommand.php` the log file will be called `HelloWorldCommand.php.log.txt` (see the
[setLogFilename](#setlogfilename-string-logfilename-) and [setDefaultLogFilenameExtension](#setdefaultlogfileextension-string-defaultlogfileextension-) function references for more information)

###### Enabled  
Logging to file can be enabled or disabled by setting this field to *true* or *false* respectively. The default is *true*. This can be overridden on a per command basis using the [setLogToFile](#setlogtofile-bool-logtofile-) function. 
 
###### Line Format 
Provide the string format that the 
[LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php) will be 
configured with by default when logging to file. You can also specify *null* to use the *Monolog* default of 
`[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.` . A new line character will automatically be 
appended to the line format when logging to file. The Line Format can also be changed per command using the 
[setFileLogLineFormat](#setfileloglineformat-string-format-) function. 

###### File Extension 
The extension of the automatically generated log file name. This is not used when a specific log file name is provided 
using the [setLogFilename](#setlogfilename-string-logfilename-) function. 

##### Console Stream
This handler will send a copy of your log records to your terminal. This helps avoid duplication of your output code as 
it allows everything that is being written to file to also be read directly from terminal when manually executing a command. The 
functionality is achieved via the *Symfony OutputInterface* used by the command and thus it is compatible with other 
symfony tools (such as the [CommandTester](http://symfony.com/doc/current/cookbook/console/console_command.html)) 
  
###### Enabled 
Logging to console can be enabled or disabled by setting this field to *true* or *false* respectively. The default is 
*true*. This can be overridden on a per command basis using the [setLogToConsole](#setlogtoconsole-bool-logtoconsole-) function. 

###### Line Format
Provide the string format that the 
[LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php) will be 
configured with by default when logging to console. You can also specify *null* to use the *Monolog* default which is 
`[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.` . A new line character will automatically be 
appended to the line format when logging to console. The Line Format can also be changed per command using the 
[setConsoleLogLineFormat](#setconsoleloglineformat-string-format-) function. 

### Parameters 
Sometimes you have a need to change the logging configuration for a single execution of a command. In the case of 
logging, this is most common when trying to debug a failure that results from a particular situation. There is thus a 
parameter to change the log level for a single execution to assist with this. 

#### `--log-level` 
The logging verbosity of a single execution can be changed by providing this parameter. It takes a string value that 
must correspond to one of *Monolog's* string names for the RFC 5424 log levels. For reference these are: 

- DEBUG 
- INFO
- NOTICE
- WARNING 
- ERROR
- CRITICAL
- ALERT
- EMERGENCY
 
A log record that reads `LOG LEVEL CHANGED: <level>` is added to explain the change in verbosity. This is useful for 
when the logs are reviewed at a later stage.
 
There is a shortcut for this parameter for historical reasons. The shortcut is: `-l`

Here is an example of changing the log level to NOTICE for a single execution
```SHELL 
$ php app/console my:super:command --log-level=NOTICE 
``` 

### Function Reference 
#### `getLogger()` 
**Parameters:** none  
**Return Value:**  
*Monolog\Logger*  
This implements the PSR-3 [LoggerInterface](https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php) and 
thus using the logger does not necessarily couple your code to the *Monolog* library.  
**Description:**  
This is the primary way to access the *Monolog* logger that has been configured for your command.  
**Availability:** After initialize

#### `setLogLevel( int $logLevel )` 
**Parameters:**
  
- *logLevel:*  
One of the log level constants defined on the Monolog [Logger](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Logger.php)
class. For reference these are:

      - Logger::DEBUG  - 100
      - Logger::INFO - 200
      - Logger::NOTICE - 250
      - Logger::WARNING - 300
      - Logger::ERROR - 400
      - Logger::CRITICAL - 500
      - Logger::ALERT - 550
      - Logger::EMERGENCY - 600

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Allows you to change the logging verbosity for a particular command from the globally defined default. Useful for commands
that go through a lifecycle of maturity   
**Availability:**  
Anytime. If used after initialize, it will result in a log record that reads `LOG LEVEL CHANGED: <level>` 
to help explain why the entire execution did not output with the same verbosity. This is helpful if you only need to increase
the verbosity for a subsection of your code.

#### `getLogLevel()` 
**Parameters:** none  
**Return Value:**  
*integer*  
Corresponds to the log level constants defined on the *Monolog* `Logger` class.
See the function reference of [setLogLevel](#setloglevel-int-loglevel-) for more information.  
**Description:**  
Get the currently configured logging verbosity.  
**Availability:** Anytime

#### `getLogLevelName()` 
**Parameters:** none  
**Return Value:**  
*string*  
Corresponds to *Monolog's* string name for the RFC 5424 log levels.
See the description of the [--log-level](#--log-level) parameter for more information.  
**Description:**  
Get the string name of the currently configured logging verbosity.  
**Availability:** Anytime

#### `setLogFilename( string $logFilename )`  
**Parameters:**

- *logFilename:*  
Name of the file to log to including file extension. This should only contain characters that are valid 
for your filesystem.  

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Provide a specific file name to log to rather than using one automatically generated by the name strategy. If the file
 does not yet exist in the log directory, it will be created otherwise it will be appended. Using this function also 
 overrides the globally configured [file_extension](#file-extension) so your provided name must include an extension if 
 you want one.  
**Availability:**  Before initialize

#### `getLogFilename( bool $fullPath = true )` 
**Parameters:**

- *fullPath:*  
Whether to return the logfile name with preceded by the directory that it is in or just the name of the file (symmetric to [setLogFilename](#setlogfilename-string-logfilename-))

**Return Value:**   *string*   
**Description:**  
Returns the name of the file being logged to.  
**Availability:**   
If no filename is provided via [setLogFilename](#setlogfilename-string-logfilename-), the automatic
name is only generated during initialize. Behaviour before either of these is undefined

#### `setDefaultLogFileExtension( string $defaultLogFileExtension )`  
**Parameters:**

- *defaultLogFileExtension:*  
The extension that will be append. This should typically start with a period.  

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Override the extension of the automatically generated log file name for this command's log file. In most cases you will probably want to set this for all your commands in your [config](#global-config). The file extension is ignored if a specific log file name is provided using the [setLogFilename()](#setlogfilename-string-logfilename-) function.    
**Availability:**   
Before initialize    

#### `getDefaultLogFileExtension()` 
**Parameters:** none  
**Return Value:**  *string*   
**Description:**  
Get the file extension added to the automatically generated logfile name.  
**Availability:** Anytime     

#### `setLogToConsole( bool $logToConsole )` 
**Parameters:**

- *logToConsole:*  
*TRUE* to enable and *FALSE* to disable

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Use this to enable or disable logging to console for this particular command. The default is *true* (i.e. *enabled*). This is useful for simplifying the output of commands that also write directly to the `OutputInterface` object of the command.   
**Availability:**   
Before initialize    

#### `isLogToConsole()` 
**Parameters:** none  
**Return Value:**  *bool*  
**Description:**  
Whether or not logging to console is enabled for this command.    
**Availability:** Anytime   

#### `setLogToFile( bool $logToFile )` 
**Parameters:**

- *logToConsole:*  
*TRUE* to enable and *FALSE* to disable

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Use this to enable or disable logging to file for this particular command. The default is *true* (i.e. *enabled*). This is useful for commands that are always run manually and thus do not require a log file.   
**Availability:**   
Before initialize    

#### `isLogToFile()` 
**Parameters:** none  
**Return Value:**  *bool*  
**Description:**  
Whether or not logging to file is enabled for this command.    
**Availability:** Anytime  

#### `setConsoleLogLineFormat( string $format )` 
**Parameters:**

- *format:*
Any format string with placeholders that is supported by *Monolog's* [LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php). You can also specify *null* to use the *Monolog* default which currently is `[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.`

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Provide the string format that the 
[LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php) will be 
configured with when logging to console. A new line character will automatically be appended to the line format provided  
**Availability:**   
Before initialize   
 
#### `getConsoleLogLineFormat()` 
**Parameters:** none  
**Return Value:**  *string*  
**Description:**  
The format configured for the Console Log Handler's LineFormatter   
**Availability:** Anytime   
 
#### `setFileLogLineFormat( string $format )` 
**Parameters:**

- *format:*
Any format string with placeholders that is supported by *Monolog's* [LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php). You can also specify *null* to use the *Monolog* default which currently is `[%datetime%] %channel%.%level_name%: %message% %context% %extra%'.`

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*  
**Description:**  
Provide the string format that the 
[LineFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/LineFormatter.php) will be 
configured with when logging to file. A new line character will automatically be appended to the line format provided  
**Availability:**   
Before initialize   

#### `getFileLogLineFormat()` 
**Parameters:** none  
**Return Value:**  *string*  
**Description:**  
The format configured for the File Log Handlers's LineFormatter   
**Availability:** Anytime 
 