# Locking 
When enabled, commands will attempt to acquire file locks during the `initialize()` phase of the command and will halt 
execution (by throwing a `\Afrihost\BaseCommandBundle\Exceptions\LockAcquireException`) if they are not able to acquire 
this lock. This then ensures that there is only a single instance of a command running on a particular machine at a time.

Under the hood, this functionality is achieved via the [Symfony Locking Component](https://symfony.com/doc/3.4/components/lock.html)
which itself asks the underlying OS's filesystem for an Exclusive Write Lock on a lockfile.

## Why is this Useful?
Ensuring that only a single instance of a command is running on a machine at a time has the following benefits:

 - Concurrent updates to the same shared storage by multiple instances of the same command are prevented (e.g. two identical 
 billing records are not saved to the database by two versions of a billing command that ran concurrently)
 - Overloading limited resources can be avoided by ensuring only a single command accesses them at a time (this could equally apply
 to local compute resources and to brittle third-party APIs)
 - Task management systems (and people) don't need to double check that a instance of an important command is not already running
 before starting another. For example, you could `cron` a command that sometimes executes for more than 60 seconds (but generally does not) 
 to be run every minute and never worry about there being two copies of the command. Similarly, if you have command that is 
 intended to be long-lived, you can ensure that its always running  by periodically just attempting to start a new one without 
 checking for an existing one.

The first point deserves some additional explanation given that [concurrent processing](https://en.wikipedia.org/wiki/Concurrent_computing)
is typically not a concern for most of us. Many commands typically make changes to some form of shared storage (such as a database, 
cache or file). This is most often done by first loading some information, then doing some computation (which may take some time )
and then saving the changes to the shared storage. If two or more of the same command are running concurrently, the resulting 
change to the storage once all commands complete is undefined. This is because it relies on the times at which they started 
(and loaded information) relative to each other, versus the times at which each command persisted changes (think 'last update wins'
 vs 'duplicate records' vs 'first update wins'). This ordering is out of the control of the code in the command (and in some
 cases is dependant on the Operating System's scheduler which is totally out of our control). The simplest naive solution
 is to avoid the concurrency and then explicitly code for it when you need it.  
 
## Example Usage 
Enabling or disabling the locking functionality must be done in the `initialize()` function before calling the `parent::initialize()`.
This can be done via the `setLocking()` function as follows:

```php
class MyCommand extends BaseCommand 
{
    // ... 
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setLocking(true);
        parent::initialize($input, $output);
    }
    
    // ...
}
```

This is a simple example. There are also options for configuring the location of the lockfiles detailed in the sections below.

**NOTE:** *The name of the lockfile is based on the configured name of the logfile for the command. You can thus cause different
executions of the same command to use different lockfiles by having them write to different logfiles. This is particularly 
useful when attempting to parallelize a large task*

### Global Config 
All the configuration options for the *Locking Enhancement* fall under the `locking` config node. As all fields 
have 'sensible' defaults, no configuration is required to start using the bundle. These options are available should you 
want to globally customise the locking functionality to your needs. The default values are shown below: 

```yaml
afrihost_base_command:
    locking:
        enabled:              true
        lock_file_folder:     null
```

#### Enabled
Acquiring a lock by default can be enabled or disabled by setting this field to *true* or *false* respectively. The default is *true*. 
This can be overridden on a per command basis using the [setLocking()](#`setlocking(-bool-$value-)`) function. 

#### Lock File Folder
The default location on the local filesystem where the lockfiles are created can be configured with this property. This can
be useful if you need to specify some specific location that all local processes have access to (such as a mounted volume in
a Docker Container)

A value of `null` (which is the default) will cause the [Symfony Locking Component](https://symfony.com/doc/3.4/components/lock.html) 
to use its default location. Typically this is the system's temporary file path ( as returned by [`sys_get_temp_dir()`](https://www.php.net/manual/en/function.sys-get-temp-dir.php))

Absolute or relative file paths can be provide. A tilde (`~`) at the start of the path will be expanded to the contents of 
the `$HOME` environment variable. Directories outside of the Symfony project directory will not be automatically created and 
must exist and be writable to the user executing the command. Relative paths will be relative to Symfony's kernel root 
directory (typically the directory in check the Kernel class is found)

### Parameters 
Sometimes you have a need to change the locking configuration for a single execution of a command. For locking, there may
be a need to do a single execution with or without locking. There is thus a parameter to enable or disable locking
at runtime to assist with this. 

#### `--locking` 
Locking can be enabled or disabled at runtime by passing either `on` or `off` as a value to the `--locking` parameter as 
follows:

```SHELL 
$ php app/console my:super:command --locking=on 
``` 


### Function Reference 
#### `setLocking( bool $value )` 
**Parameters:** 
- *value:*  
'true' to enable and 'false' to disable

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*    
**Description:**  
This function should be called within the `initialize()` function prior to calling `parent::initialize()` in order to 
enable or disable locking  
**Availability:** Before initialize

#### `isLocking()` 
**Parameters:** none  
**Return Value:**  *bool*  
**Description:** 
Whether or not locking is currently enabled
**Availability:** Anytime

#### `setLockFileFolder( string $lockFileFolder )` 
**Parameters:** 
- *lockFileFolder:*  
    A value of `null` will cause the [Symfony Locking Component](https://symfony.com/doc/3.4/components/lock.html) 
    to use its default location. Typically this is the system's temporary file path (as returned by [`sys_get_temp_dir()`](https://www.php.net/manual/en/function.sys-get-temp-dir.php))

    Absolute or relative file paths can be provide. A tilde (`~`) at the start of the path will be expanded to the contents of 
    the `$HOME` environment variable. Directories outside of the Symfony project directory will not be automatically created and 
    thus must exist and be writable to the user executing the command. Relative paths will be relative to Symfony's kernel root 
    directory (typically the directory in check the Kernel class is found)

**Return Value:**  
*Afrihost\BaseCommandBundle\Command\BaseCommand*    
**Description:**  
This function should be called within the `initialize()` function prior to calling `parent::initialize()` in order to 
change the location at which the lockfiles are stored. This can be useful if you need to specify some specific location 
that all local processes have access to (such as a mounted volume ina Docker Container)  
**Availability:** Before initialize


#### `getLockFileFolder()` 
**Parameters:** none  
**Return Value:**  *string* | *null*   
**Description:** 
Returns the currently configured directory where lockfiles are stored  
**Availability:** After Initialize (prior to the `initialize()` function being called, function does not error but its behaviour is undefined)
