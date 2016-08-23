<?php

class User {

    var $username;
    var $email;
    var $gravatar;
    var $message;
    var $roles;
    var $profile;


    function setAsGuest() {
        $this->username = "guest";
        $this->email = "";
        $this->roles = [];
    }

    function __construct() {
        $this->setAsGuest();
        $f3 = \Base::instance();
        $f3->set("SESSION.user", $this);
    }

    function login($username, $password) {
        //TODO: Check db
        if(true) {
            $this->username = $username;
            $this->email = "dimkasta@yahoo.gr";
            $this->message = "Successfully Logged in"; //TODO: Multilingual
            $this->roles = $this->getRoles(); //Setting as plain user
            //TODO: Check for other roles
            //TODO: Load profile info
        }
        else {
            $this->message = "Unsuccessful attempt"; //TODO: Multilingual
            //TODO: Limit unsuccesful attempts per ip, etc
        }
        return $this;
    }

    function logout() {
        $this->setAsGuest();
        return $this;
    }

    function getGravatar($email, $size) {
        return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=mm&s=" . $size;
    }

    function getRoles() {
        //TODO: Get from db
        //It should have by default 1 as administrator, and 2 as plain user
        return [2];
    }
}