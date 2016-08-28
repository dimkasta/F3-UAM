<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 28/08/16
 * Time: 04:07
 */

namespace Iconic;


class Gravatar
{
    use Log;

    private $url;

    public function __construct($email, $size) {
        $this->enableLog(true);
        $this->url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=mm&s=" . $size;
    }

    public function getUrl() {
        if (!filter_var($this->url, FILTER_VALIDATE_URL) === false) {
            $this->log("Valid Gravatar Url");
            return $this->url;
        }
        else {
            $this->log("Invalid Gravatar Url");
            return "";
        }
    }

    public function __toString()
    {
        return $this->getUrl();
    }
}