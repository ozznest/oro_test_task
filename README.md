Console Command Chaining
========================
To run application you should run command "make build" and than "make up"

Than run command "make composerInstall"

To run test you should run make test

For showing program result run commands "make foo" and "make bar"

If you want to run console command bar:command as member of chain for foo:command your need:

1) You service for bar:command must implement ChainableInterface and its method getName must return name of root
   command: foo:command
