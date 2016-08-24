<?php
$f3=require('lib/base.php');
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

        echo $f3->get("POST.username") . '=' . $f3->get("POST.password");

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

$f3->run();