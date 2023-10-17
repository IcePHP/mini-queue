<?php

namespace IcePHP\MiniQueue;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Error;
use Exception;
use MiniQueue\ORM\Job;

class Worker
{
    private string $type;
    private mixed $callback;
    private int $concurrency = 20;
    private EntityManager $entityManager;
    private mixed $logger = null;
    public function __construct(EntityManager $entityManager, string $type)
    {
        $this->callback = [$this, 'method'];
        $this->setEM($entityManager);
        $this->type = $type ?? "__default__";
    }
    private function method()
    {
    }
    function setEM(EntityManager $entityManager): self
    {
        $this->entityManager =  $entityManager;
        return $this;
    }
    public function setLogger(callable $logger)
    {
        $this->logger = $logger;
    }
    public function  dispatcher(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }
    private function setConcurrency(int $concurrency = 10): void
    {
        $this->concurrency = $concurrency;
    }

    function log(string $txt)
    {
        if ($this->logger == null) {
            $log = $txt.PHP_EOL;
            echo $log;
        } else {
            ($this->logger)($txt);
        }
    }

    public function run()
    {
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria->orderBy(['created_at' => "ASC"])
            ->setMaxResults($this->concurrency)
            ->andWhere(\Doctrine\Common\Collections\Criteria::expr()->lte('delay', new DateTime('now', new DateTimeZone('UTC'))))
            ->andWhere(\Doctrine\Common\Collections\Criteria::expr()->eq('type', $this->type))
            ->andWhere(\Doctrine\Common\Collections\Criteria::expr()->eq('state', 'idle'));
        /**
         * @var Job[]
         */
        $jobs = $this->entityManager->getRepository(Job::class)->matching($criteria)->toArray();
        foreach ($jobs as $job) {
            $job->setEM($this->entityManager);
            if ($this->callback != null) {
                $job->setState('active');
                $job->setLogger(fn (string $log) => $this->log($log));
                $job->save();
                $job->log('Active');
                try {
                    ($this->callback)($job, function () use ($job) {
                        $job->setState('completed');
                        $job->save();
                        $job->log('Completed');
                    });
                } catch (Error $e) {
                    $job->setState('failed');
                    $job->save();
                    $this->log('Error ' . $e->getMessage());
                } catch (Exception $e) {
                    $job->setState('failed');
                    $job->save();
                    $this->log('Exception ' . $e->getMessage());
                }
            }
        }
    }
}
