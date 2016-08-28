<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 28/08/16
 * Time: 03:11
 */

namespace Iconic;


trait Message
{
    private $messages;

    public function __construct() {
        $this->messages = [];
    }

    public function show() {
        foreach ($this->messages as $message) {
            echo $message . "<br>";
        }
    }

    public function get() {
        return $this->messages;
    }
}