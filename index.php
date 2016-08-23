<?php
$f3=require('lib/base.php');
$f3->set('DEBUG',3);
$f3->config('config.ini');

$f3->route('GET /',
	function($f3) {


//        new Session();
//        $user = new User();
//        $user->login("dimkasta", "12345678");
//
//        $user2 =  $f3->get("SESSION.user");
//        echo $user2->username;
//        echo $user2->email;
//
//        $user2->logout();
//
//        $user3 = $f3->get("SESSION.user");
//        echo $user3->username;
//        echo $user3->email;
	}
);



$f3->run();
