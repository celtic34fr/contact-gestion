<?php

namespace Celtic34fr\ContactGestion\FormEntity;

class MailingExtract
{
    private ?array $fields;

    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function addField(string $field)
    {
        if (in_array($field, $this->fields)) {
            return false;
        }

        $this->fields[$field] = $field;
        return $this;
    }

    public function removeField(string $field) {
        if (!in_array($field, $this->fields)) {
            return false;
        }
        unset($this->fields[$field]);
        return $this;
    }

    public function setFields(array $fields) {
        $this->fields = $fields;
        return $this;
    }
}
