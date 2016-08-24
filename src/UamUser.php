<?php

class UamUser {

    var $username;
    var $email;
    var $gravatar;
    var $message;
    var $roles;
    var $profile;


    function setAsGuest() {
        $this->username = "guest";
        $this->email = "";
        unset($this->roles);
    }

    function __construct() {
        $this->setAsGuest();
        $f3 = \Base::instance();
        $f3->set("SESSION.uamUser", $this);

        \WebUAM::initialize();
    }

    function login($username, $password) {
        $loginResult = \WebUAM::doLogin($username, $password);

        if($loginResult->success) {
            $this->username = $username;
            $this->email = "dimkasta@yahoo.gr";
            $this->roles = \WebUAM::getRoles();
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

    function isUser() {
        return $this->username != 'guest';
    }

    function isAdmin() {
        return $this->isInRole(1);
    }

    function isInRole($role_id) {
        return \WebUAM::isInRole($role_id);
    }

    function subscribe($username, $password, $email) {
        return \WebUAM::doSubscription($username, $password, $email);
    }
}