<?php

namespace IcePHP\MiniQueue;
// bootstrap.php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use IcePHP\MiniQueue\ORM\Job;
use IcePHP\MiniQueue\ORM\Meta;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;

class MiniQueue
{
    private EntityManager $entityManager;
    private array $config;
    function __construct(array $config, $isDevMode = true)
    {
        // Create a simple "default" Doctrine ORM configuration for Attributes
        $orm_config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array(__DIR__ . "/orm"),
            isDevMode: $isDevMode,
        );

        $this->config = $config;
        // configuring the database connection
        $connection = DriverManager::getConnection($config, $orm_config);

        // obtaining the entity manager
        $this->entityManager = new EntityManager($connection, $orm_config);
    }

    function pid():int|false{
        return getmypid();
    }

    function getName(){
       return __FILE__;
    }

    function queue(array $params): Job{
       return Job::create($params, $this->entityManager);
    }
    function getMeta(string $name): Meta|null{
        return Meta::find(['name'=> $name], $this->entityManager);
    }
    function setMeta(string $name, mixed $value): Meta{
        return (new Meta($this->entityManager))->setName($name)-> setValue($value)->save();
    }
    function log (string $txt){
        $log = $txt.PHP_EOL;
        echo $log;
    }

    function ensureSingleProcess(){
        $metaName = $this->getName().".pid";
        $meta = $this->getMeta($metaName);
        if($meta == null){
            $meta = $this->setMeta($metaName, $this->pid());
            return;
        }
        $running = posix_kill($meta->getValue(), 0);
        if($running){
            die("Another process already running".PHP_EOL);
        }else{
            $meta->setValue($this->pid());
            $meta->save();
        }
    }
    // process indefinate
    function process($type, callable $callback): void{
       $this->ensureSingleProcess();
        $worker = (new Worker($this->entityManager, $type))->dispatcher($callback);
        while(true){
            $worker->run();
        }
    }

    function bootstrap(){
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($this->entityManager);
        try{
            $tool->createSchema($metadata);
        }catch(Exception){
            try{
            $tool->updateSchema($metadata);
            }catch(Exception){}
        }
    }
    function console(array $commands = []){
        ConsoleRunner::run(
            new SingleManagerProvider($this->entityManager),
            $commands
        );
    }
}
