<?php

namespace Iconic;

class Email extends Property
{
    public function sanitize()
    {
        $this->log("Sanitizing");
        $this->dirty = filter_var($this->dirty, FILTER_SANITIZE_EMAIL);
    }

    public function isPropertyValid()
    {
        $this->log("Is email valid?");
        if(!is_string($this->dirty)) {
            $this->log("Not a string");
            array_push($this->issues, "non_string_email");
        }
        if(!filter_var($this->dirty,FILTER_VALIDATE_EMAIL)) {
            $this->log("Invalid Email");
            array_push($this->issues, "invalid_email");
        }
    }
}