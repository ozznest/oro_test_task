Console Command Chaining
========================
To run application you shoulr run command make:build than make :up

To run test you should run make:test

For showing program result run commands "make foo" and "make bar"

If you want to run console command bar:command as member of chain for foo:command your need:

1) You service for bar:command must implements ChainableInterface and its method getName must return name of root
2) You service for foo:command must implements RootCommandInterface and its method command