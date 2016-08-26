<?php
$f3=require('../../fatfree-master/lib/base.php');
$f3->set('DEBUG',3);
$f3->config('config.ini');
$f3->set('db',new \DB\SQL('mysql:host=' . $f3->dbHost . ';port=' . $f3->dbPort . ';dbname=' . $f3->dbName , $f3->dbUser , $f3->dbPassword));

\UamUser::get();

$f3->route('GET /',
	function($f3) {
        $f3->view = "home.html";
        echo \Template::instance()->render('master.html');
	}
);

$f3->route('GET /login',
    function($f3) {
        $f3->view = "login.html";
        echo \Template::instance()->render('master.html');
    }
);

$f3->route('POST /dologin',
    function($f3) {
        $result = $f3->get("SESSION.uamUser")->login($f3->get("POST.username"), $f3->get("POST.password"));

        if($result->success) {
            $f3->reroute('/');
        }
        else {
            //TODO: Fix messages and how they are passed by the framework
            $f3->reroute('/login');
        }
    }

);

$f3->route('GET /dologout',
    function($f3) {
        $result = $f3->get("SESSION.uamUser")->logout();
        $f3->reroute('/');
    }
);

$f3->route('GET /subscribe',
    function($f3) {
        $f3->view = "subscribe.html";
        echo \Template::instance()->render('master.html');
    }
);


$f3->route('POST /dosubscribe',
    function($f3) {
        $result = $f3->get("SESSION.uamUser")->subscribe($f3->get("POST.username"), $f3->get("POST.password"), $f3->get("POST.email"));

        echo JSON_ENCODE($result);

        if($result->success) {
            $f3->reroute('/');
        }
        else {
            //TODO: Fix messages and how they are passed by the framework
            $f3->reroute('/login');
        }
    }
);

$f3->route('GET /users',
    function($f3) {
        if($f3->get("SESSION.uamUser")->isAdmin()) {

            $f3->set("uamUsers", $f3->get('SESSION.uamUser')->getAll());

            $f3->view = "users.html";
            echo \Template::instance()->render('master.html');
        }
        else {
            $f3->reroute('/');
        }
    }
);

$f3->route('GET /roles',
    function($f3) {
        if($f3->get("SESSION.uamUser")->isAdmin()) {
            $f3->view = "roles.html";
            echo \Template::instance()->render('master.html');
        }
        else {
            $f3->reroute('/');
        }
    }
);

$f3->route('POST /updaterole',
    function($f3) {
        if(\Uamfunctions::updateRole()) {
            //TODO: success messages?
            $f3->reroute('roles');
        }
        else {
            //TODO: Errors?
            $f3->reroute('roles');
        }
    }
);

$f3->route('POST /deleterole',
    function($f3) {
        if(\Uamfunctions::deleteRole()) {
            //TODO: success messages?
            $f3->reroute('roles');
        }
        else {
            //TODO: Errors?
            $f3->reroute('roles');
        }
    }
);

$f3->route('POST /newrole',
    function($f3) {
        if(\Uamfunctions::newRole()) {
            //TODO: success messages?
            $f3->reroute('roles');
        }
        else {
            //TODO: Errors?
            $f3->reroute('roles');
        }
    }
);

$f3->route('GET /myprofile',
    function($f3) {
        if($f3->get("SESSION.uamUser")->isUser()) {
            $f3->view = "myprofile.html";
            echo \Template::instance()->render('master.html');
        }
        else {
            $f3->reroute('/login');
        }
    }
);

$f3->run();