<?php

namespace Celtic34fr\ContactGestion\FormEntity;

use Symfony\Component\Validator\Constraints as Assert;

class MailingExtract
{
    #[Assert\Type('string')]
    protected string $type;

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
