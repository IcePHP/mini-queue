<?php

namespace IcePHP\MiniQueue\ORM;

use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'meta')]
#[ORM\HasLifecycleCallbacks]
class Meta
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int|null $id = null;
    #[ORM\Column(
        type: 'string',
        nullable: false,
        unique: true
    )]
    private string $name;
    #[ORM\Column(
        type: 'datetime',
        nullable: false,
        options: ["default" => 0]
    )]
    private \DateTime $created_at;
    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;

    #[ORM\Column(type: 'text', nullable: true, options: ["default" => null])]
    private string| null $value = null;
    private EntityManager $entityManager;
    function __construct(EntityManager $entityManager)
    {
        $this->setEM($entityManager);
        $this->created_at = new \DateTime("now");
    }
    function setEM(EntityManager $entityManager): Meta
    {
        $this->entityManager =  $entityManager;
        return $this;
    }
    public function getId(): int|null
    {
        return $this->id;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updated_at;
    }

    public function setValue(mixed $value = null): self
    {
        if ($value != null) {
            $this->value = json_encode($value);
        } else {
            $this->value = $value;
        }
        return $this;
    }

    public function getValue(): mixed
    {
        if ($this->value != null) {
            return json_decode($this->value, true);
        } else {
            return null;
        }
    }


    static function create(array $options, EntityManager $entityManager): self
    {
        $config = new Meta($entityManager);
        $config->setName($options['name']);
        $config->setValue($options['value']);
        return $config->save();
    }

    static function find(array $options, EntityManager $entityManager): self | null
    {
        /**
         * @var Meta|null
         */
        $meta =  $entityManager->getRepository(Meta::class)->findOneBy($options);
        if ($meta) $meta->setEM($entityManager);
        return $meta;
    }
    function save(): self
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
