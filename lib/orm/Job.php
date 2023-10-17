<?php

namespace IcePHP\MiniQueue\ORM;

use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jobs')]
#[ORM\HasLifecycleCallbacks]
class Job
{
    const TYPE = '__default__';
    const DELAY = 0;
    const MILLISECOND_TO_SEC = 1000;
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column(
        type: 'string',
        nullable: true,
    )]
    private string| null $name;
    #[ORM\Column(
        type: 'datetime',
        nullable: false,
        options: ["default" => 0]
    )]
    private \DateTime $delay;
    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;
    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;
    #[ORM\Column(type: 'string', options: ["default" => "idle"])]
    private string $state = 'idle';

    #[ORM\Column(type: 'string', options: ["default" => "__default__"])]
    private string $type = Job::TYPE;

    #[ORM\Column(type: 'text', nullable: true, options: ["default" => null])]
    private string| null $data = null;
    private mixed $logger = null;
    private EntityManager $entityManager;
    function __construct(EntityManager $entityManager)
    {
        $this->setEM($entityManager);
        $this->created_at = new \DateTime("now");
    }
    function setEM(EntityManager $entityManager): Job
    {
        $this->entityManager =  $entityManager;
        return $this;
    }
    public function getId(): int|null
    {
        return $this->id;
    }
    public function setLogger(callable $logger)
    {
        $this->logger = $logger;
    }
    public function  log(string $text)
    {
        if ($this->logger == null) {
            echo $text;
        } else {
            ($this->logger)("Job " . $this->getId() . ' => ' . $text);
        }
    }
    public function getName(): string| null
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setDelay($millisecond = 0): void
    {
        $this->delay = (new \DateTime())->setTimestamp($this->created_at->getTimestamp());
        $this->delay =  $this->delay->setTimestamp($this->delay->getTimestamp() + ($millisecond / Job::MILLISECOND_TO_SEC));
    }
    public function getDelay(): int
    {
        return ($this->delay->getTimestamp() ?? 0) * Job::MILLISECOND_TO_SEC;
    }


    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    public function setData(array| null $data = null): void
    {
        if ($data != null) {
            $this->data = json_encode($data);
        } else {
            $this->data = $data;
        }
    }

    public function getData(): array | null
    {
        if ($this->data != null) {
            return  json_decode($this->data, true);
        } else {
            return null;
        }
    }


    static function create(array $options, EntityManager $entityManager): Job
    {
        $job = new Job($entityManager);
        $job->setType($options['type'] ?? Job::TYPE);
        $job->setData($options['data']);
        if ($options['delay'] instanceof  \DateTimeInterface) {
            $options['delay']->setTimezone(new DateTimeZone('UTC'));
            $delay = $options['delay']->getTimestamp() - (new \DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            $job->setDelay($delay * Job::MILLISECOND_TO_SEC);
        } else {
            $job->setDelay($options['delay'] ?? Job::DELAY);
        }
        return $job->save();
    }

    function save(): Job
    {
        $this->entityManager->persist($this);
        $this->entityManager->flush();
        return $this;
    }
    function remove(): bool
    {
        $this->entityManager->remove($this);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Gets triggered only on insert
     * 
     */
    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->updated_at = $this->created_at ??= new \DateTime("now", new DateTimeZone('UTC'));
    }

    /**
     * Gets triggered every time on update
     *
     */
    #[ORM\PreUpdate]
    public function onPreUpdate()
    {
        $this->updated_at = new \DateTime("now", new DateTimeZone('UTC'));
    }
}
