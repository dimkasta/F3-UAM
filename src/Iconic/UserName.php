<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 28/08/16
 * Time: 03:49
 */

namespace Iconic;


class UserName extends Property
{
    public function sanitize()
    {
        $this->log("Sanitizing");
        $this->dirty = htmlspecialchars ($this->dirty);
    }

    public function isPropertyValid()
    {
        $this->log("Is username valid?");
        if(!is_string($this->dirty)) {
            $this->log("Not a string");
            array_push($this->issues, "non_string_username");
        }
        if(strlen($this->dirty) < 8) {
            $this->log("Short username");
            array_push($this->issues, "short_username");
        }

        if(strlen($this->dirty) > 20) {
            $this->log("Long username");
            array_push($this->issues, "long_username");
        }
    }
}