<?php

namespace Celtic34fr\ContactGestion\MySQLiEntity;

use Celtic34fr\ContactCore\MySQLiEntity\MSICliInfos;
use DateTime;
use Exception;

class MSIDemandes
{
    private ?int $id = null;
    private string $created_at;
    private ?string $treated_at = null;
    private ?string $sujet = null;
    private ?string $demande = null;
    private ?bool $contact_me = null;
    private ?int $reponse_id = null;
    private ?string $send_at = null;
    private ?int $client_id = null;
    private ?string $closed_at = null;

    private ?MSICliInfos $client = null;
    private ?MSIResponse $reponse = null;

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
     * @return DateTime
     * @throws Exception
     */
    public function getCreatedAtDT(): DateTime
    {
        return new DateTime($this->created_at);
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param DateTime $created_at
     */
    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return string|null
     */
    public function getTreatedAt(): ?string
    {
        return $this->treated_at;
    }

    /**
     * @return DateTime|null
     * @throws Exception
     */
    public function getTreatedAtDT(): ?DateTime
    {
        return $this->treated_at ? new DateTime($this->treated_at) : null;
    }

    /**
     * @param DateTime|null $treated_at
     */
    public function setTreatedAt(?DateTime $treated_at): void
    {
        $this->treated_at = $treated_at;
    }

    /**
     * @return string|null
     */
    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    /**
     * @param string|null $sujet
     */
    public function setSujet(?string $sujet): void
    {
        $this->sujet = $sujet;
    }

    /**
     * @return string|null
     */
    public function getDemande(): ?string
    {
        return $this->demande;
    }

    /**
     * @param string|null $demande
     */
    public function setDemande(?string $demande): void
    {
        $this->demande = $demande;
    }

    /**
     * @return bool|null
     */
    public function getContactMe(): ?bool
    {
        return $this->contact_me;
    }

    /**
     * @param bool|null $contact_me
     */
    public function setContactMe(?bool $contact_me): void
    {
        $this->contact_me = $contact_me;
    }

    /**
     * @return int|null
     */
    public function getReponseId(): ?int
    {
        return $this->reponse_id;
    }

    /**
     * @param int|null $reponse_id
     */
    public function setReponseId(?int $reponse_id): void
    {
        $this->reponse_id = $reponse_id;
    }

    /**
     * @return MSICrmResponse|null
     */
    public function getReponse(): ?int
    {
        return $this->reponse;
    }

    /**
     * @param MSICrmResponse $reponse
     */
    public function setReponse(MSICrmResponse $reponse): void
    {
        $this->reponse = $reponse;
        $this->reponse_id = $reponse->getId();
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
     * @throws Exception
     */
    public function getSendAtDT(): ?DateTime
    {
        return $this->send_at ? new DateTime($this->send_at) : null;
    }


    /**
     * @param DateTime|null $send_at
     */
    public function setSendAt(?DateTime $send_at): void
    {
        $this->send_at = $send_at;
    }

    /**
     * @return int|null
     */
    public function getClientId(): ?int
    {
        return $this->client_id;
    }

    /**
     * @param int|null $client_id
     */
    public function setClientId(?int $client_id): void
    {
        $this->client_id = $client_id;
    }
    /**
     * @return MSICrmCliInfos|null
     */
    public function getClient(): ?int
    {
        return $this->client;
    }

    public function setClient(MSICrmCliInfos $client)
    {
        $this->client = $client;
        $this->client_id = $client->getId();
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
     * @throws Exception
     */
    public function getClosedAtDT(): ?DateTime
    {
        return $this->closed_at ? new DateTime($this->closed_at) : null;
    }

    /**
     * @param DateTime|null $closed_at
     */
    public function setClosedAt(?DateTime $closed_at): void
    {
        $this->closed_at = $closed_at;
    }

}
