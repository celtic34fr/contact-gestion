<?php

namespace Celtic34fr\ContactGestion\FormEntity;

class MailingExtract
{
    protected string $list;

    protected string $fileName;

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
