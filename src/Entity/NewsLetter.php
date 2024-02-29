<?php

namespace Celtic34fr\ContactGestion\Entity;

use Celtic34fr\ContactCore\Entity\Clientele;
use Celtic34fr\ContactGestion\Enum\NewsEnums;
use Celtic34fr\ContactGestion\Repository\NewsLetterRepository;
use Celtic34fr\ContactGestion\Validator\Constraint as CustomAssert;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NewsLetterRepository::class)]
#[ORM\Table(name:'newsletters')]
#[ORM\HasLifecycleCallbacks]
/**
 * classe NewsLetter
 * 
 * - created_at : date de création de l'enregistrement et de début de l'accord
 * - ended_at   : date de fin de l'accord, champ facultatif
 * - client     : relation globale ayant donné son accord, relation ManyToOne vers la table Clientele
 * - news       : nature de l'accord, Cf. l'énumération NewsEnums
 */
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
    /**
     * date de création, champ obligatoire
     * @var DateTime
     */
    private DateTime $created_at;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\DateTime]
    /**
     * date de fin ou clôture d'envoi de la lettre d'informations, champ facultatif
     * @var DateTime|null
     */
    private ?DateTime $ended_at = null;

    #[ORM\ManyToOne(targetEntity: Clientele::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type(Clientele::class)]
    /**
     * lien vers l'internaute (informations fixes), champ obligatoire
     * @var Clientele
     */
    private Clientele $client;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[CustomAssert\NewsType]
    /**
     * type de l'internaute, Cf. Enum NewsEnums, champ obligatoire
     * @var string
     */
    private string $news;


    #[ORM\PrePersist]
    public function beforPersist(PrePersistEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        $this->created_at = new DateTimeImmutable('now');
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

    public function getNews(): string
    {
        return $this->news;
    }

    public function setNews(string $news): bool|self
    {
        if (NewsEnums::isValid($news)) {
            $this->news = $news;
            return $this;
        }
        return false;
    }
}
