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
        $this->email = "guest";
        $this->gravatar = \Uamfunctions::getGravatar("guest", 80);
        $this->roles = [];
    }

    public static function get() {
        $f3 = \Base::instance();
        if(empty($f3->get("SESSION.uamUser"))) {
            $user = new UamUser();
            $user->setAsGuest();
            $f3->set("SESSION.uamUser", $user);
        }
        else {
            $user = $f3->get("SESSION.uamUser");
        }

        \Uamfunctions::initialize();
    }

    function login($username, $password) {
        $loginResult = \Uamfunctions::doLogin($username, $password);

        if($loginResult->success) {
            //$this->username = $username;
            //$this->email = "dimkasta@yahoo.gr";
            //$this->gravatar = \Uamfunctions::getGravatar()
            $this->roles = \Uamfunctions::getRoles();
            //TODO: Enhancement: Load profile info
        }
        else {
            $this->message = "Unsuccessful attempt"; //TODO: Multilingual
            //TODO: Limit unsuccesful attempts per ip, etc
        }
        return $loginResult;
    }

    function logout() {
        $this->setAsGuest();
        return $this;
    }

    function isUser() {
        return $this->username != 'guest';
    }

    function isAdmin() {
        return $this->isInRole(1);
    }

    function isInRole($role_id) {
        return \Uamfunctions::isInRole($role_id);
    }

    function subscribe($username, $password, $email) {
        return \Uamfunctions::doSubscription($username, $password, $email);
    }
}