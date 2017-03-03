<?php

 require __DIR__ . '/bootstrap.php';
 $configurator = new Nette\Configurator;
 $configurator->setDebugMode(true);
 $configurator->enableDebugger(__DIR__ . '/../log');
 $configurator->setTempDirectory(__DIR__ . '/../temp');
 $configurator->createRobotLoader()
         ->addDirectory(__DIR__)
         ->addDirectory(__DIR__ . '/../src')
         ->register();
 $configurator->addConfig(__DIR__ . '/test.neon');

 $container = $configurator->createContainer();

  \Tracy\Debugger::enable(true);
  \Tracy\Debugger::setLogger($container->getByType(Tracy\ILogger::class));

  throw new Exception('test');
 
 

 
 

 