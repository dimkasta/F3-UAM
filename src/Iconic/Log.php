<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 28/08/16
 * Time: 03:25
 */

namespace Iconic;


trait Log
{
    private $messages;
    private $enabled;

    public function enableLog($enabled) {
        $this->messages = [];
        $this->enabled = $enabled;
    }

    public function log($message) {
        if($this->enabled) {
            array_push($this->messages, $message);
        }
    }

    public function showLog() {
        foreach ($this->messages as $message) {
            echo $message . "<br>";
        }
    }

    public function getLog() {
        return $this->messages;
    }
}