Console Command Chaining
========================

A Symfony bundle that implements command chaining functionality. It allows other 
bundles in the application to register their console commands to be members of 
a command chain, which are executed when a user runs the main command.
This bundle disables ability standalone execution of commands that are registered 
as members of the command chain.

Configuration
-------------

The bundle should be registered in AppKernel.php by adding 

```
new ChainCommandBundle\ChainCommandBundle(),
```

in registerBundles() method.


To format console log in required way you should modify a configuration of the application:

```
services:
    log_formatter:
        class: Symfony\Bridge\Monolog\Formatter\ConsoleFormatter
        arguments:
            - "[%%datetime%%] %%message%%\n"

monolog:
    handlers:
        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            formatter: log_formatter
            channels: [!event]
        console:
            type:   console
            channels: [!event, !doctrine]
```

This bundle requires also to configure chain_command service:

```
output_proxy:
  class: ChainCommandBundle\Component\ProxyOutputComponent


chain_command:
  class: ChainCommandBundle\Component\ChainCommandComponent
  arguments: ['@logger', '@output_proxy']
```


Usage
-----

Every command that you want to enable to use ChainCommandBundle should inherit 
from ChainCommand class. By default every command is a master command. During 
configuration of a command you can assign the command to chain of another command:

```
protected function configure() {
    $this
        ->setName('bar:hi')
        ->setDescription('Command to test ChainCommandBundle')
        ->attachTo('foo:hello');
}
```


Demonstration
-------------

We have two bundles: `foo:hello` and `bar:hi`. The `bar:hi` command is attached to `foo:bar`
so it can not be executed on its own:

```bash
$ php app/console bar:hi
Error: bar:hi command is a member of foo:hello command chain and cannot be executed on its own.
```

At the same time `foo:hello` is the master command contains a chain, so executing it produces output like this:

```bash
$ php app/console foo:hello
Hello from Foo!
Hi from Bar!
```

Above operation produces log recores:

```bash
$ cat app/logs/dev.log 
[2016-06-27 13:30:25] foo:hello is a master command of a command chain that has registered member commands
[2016-06-27 13:30:25] bar:hi registered as a member of foo:hello command chain
[2016-06-27 13:30:25] Executing foo:hello command itself first:
[2016-06-27 13:30:25] Hello from Foo!
[2016-06-27 13:30:25] Executing foo:hello chain members:
[2016-06-27 13:30:25] Hi from Bar!
[2016-06-27 13:30:25] Execution of foo:hello chain completed.
```

Final notes
-----------

> Probably there is a better way of catching commands output than the ones that is implemented in this bundle.