<?php

namespace Celtic34fr\ContactGestion\MySQLiEntity;

use DateTime;

class MSICrmResponse
{
    private ?int $id = null;
    private ?string $reponse = null;
    private ?string $send_at = null;
    private ?string $closed_at = null;
    private ?int $contact_id = null;
    private array $categories = [];
    private int $operateur_id;

    private ?MSIDemandes $contact;
    private ?MSIUser $operateur = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    /**
     * @param string|null $reponse
     */
    public function setReponse(?string $reponse): void
    {
        $this->reponse = $reponse;
    }

    /**
     * @return string|null
     */
    public function getSendAt(): ?string
    {
        return $this->send_at;
    }

    /**
     * @return DateTime|null
     * @throws \Exception
     */
    public function getSendAtDT(): ?DateTime
    {
        return $this->send_at ? new DateTime($this->send_at) : null;
    }

    /**
     * @param string|null $send_at
     */
    public function setSendAt(?string $send_at): void
    {
        $this->send_at = $send_at;
    }

    /**
     * @return string|null
     */
    public function getClosedAt(): ?string
    {
        return $this->closed_at;
    }

    /**
     * @return DateTime|null
     */
    public function getClosedAtDT(): ?DateTime
    {
        return $this->closed_at ? new DateTime($this->closed_at) : null;
    }

    /**
     * @param string|null $closed_at
     */
    public function setClosedAt(?string $closed_at): void
    {
        $this->closed_at = $closed_at;
    }

    /**
     * @return int|null
     */
    public function getContactId(): ?int
    {
        return $this->contact_id;
    }

    /**
     * @param int|null $contact_id
     */
    public function setContactId(?int $contact_id): void
    {
        $this->contact_id = $contact_id;
    }

    /**
     * @return CrmDemandes|null
     */
    public function getContact(): ?CrmDemandes
    {
        return $this->contact;
    }

    /**
     * @param CrmDemandes|null $contact
     */
    public function setContact(?CrmDemandes $contact): void
    {
        $this->contact = $contact;
        $this->contact_id = $contact->getId();
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return int
     */
    public function getOperateurId(): int
    {
        return $this->operateur_id;
    }

    /**
     * @param int $operateur_id
     */
    public function setOperateurId(int $operateur_id): void
    {
        $this->operateur_id = $operateur_id;
    }

    /**
     * @return User
     */
    public function getOperateur(): User
    {
        return $this->operateur;
    }

    /**
     * @param MSIUser $operateur
     */
    public function setOperateur(MSIUser $operateur): void
    {
        $this->operateur = $operateur;
        $this->operateur_id = $operateur->getId();
    }

}
