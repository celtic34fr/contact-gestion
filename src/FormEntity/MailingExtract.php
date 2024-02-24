<?php

namespace Celtic34fr\ContactGestion\FormEntity;

use Symfony\Component\Validator\Constraints as Assert;

class MailingExtract
{
    #[Assert\Type('string')]
    protected string $type;

    #[Assert\Type('string')]
    protected string $customer;

    #[Assert\Type('bool')]
    protected bool $active;

    #[Assert\Type('int')]
    protected int $close_from;

    #[Assert\Type('string')]
    protected string $list;

    #[Assert\Type('string')]
    protected string $fileName;


    
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    
    public function getCustomer(): string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): self
    {
        $this->customer = $customer;
        return $this;
    }
    
    public function getActive(): bool
    {
        return (bool) $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    public function getCloseFrom(): int
    {
        return (bool) $this->close_from;
    }

    public function setCloseFrom(int $close_from): self
    {
        $this->close_from = $close_from;
        return $this;
    }
    
    public function getList(): string
    {
        return $this->list;
    }

    public function setList(string $list): self
    {
        $this->list = $list;
        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }
}
