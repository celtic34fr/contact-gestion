<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion\Entity;

use Bolt\Extension\Celtic34fr\ContactCore\Entity\Clientele;
use Bolt\Extension\Celtic34fr\ContactGestion\Repository\NewsLetterRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewsLetterRepository::class)]
#[ORM\Table(name:'newsletters')]
class NewsLetter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\DateTime]
    private DateTime $created_at;          // date de création

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    private ?DateTime $ended_at = null;    // date de fin ou clôture d'envoi de la lettre d'informations

    #[ORM\OneToOne(targetEntity: Clientele::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(Clientele::class)]
    private Clientele $client;              // lien vers l'internaute (informations fixes)

    public function __construct()
    {
        $this->created_at = new \DateTime('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getEndeddAt(): ?\DateTime
    {
        return $this->ended_at;
    }

    public function setEndedAt(?\DateTime $ended_at): self
    {
        $this->ended_at = $ended_at;
        return $this;
    }

    public function getClient(): Clientele
    {
        return $this->client;
    }

    public function setClient(Clientele $client): self
    {
        $this->client = $client;
        return $this;
    }
}
