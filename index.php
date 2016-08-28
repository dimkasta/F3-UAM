<?php

use Iconic\Email;
//$f3=require('../../fatfree-master/lib/base.php');

$f3=require('lib/base.php');

$f3->set('DEBUG',3);
$f3->config('config.ini');
//$f3->set('db',new \DB\SQL('mysql:host=' . $f3->dbHost . ';port=' . $f3->dbPort . ';dbname=' . $f3->dbName , $f3->dbUser , $f3->dbPassword));

$f3->route('GET /',
    function($f3) {
        $email = "dimkasta@yahoo.gr";
//        $email = "d@j.com";
        $ob = new Email($email, true);
//        echo $ob;
        $ob->showLog();

//        $u = new \Iconic\UserName("dimasta", true);
//        echo $u;

        $g = new \Iconic\Gravatar($ob, 80);
        $g->showLog();
        echo $g;


//        $u->showLog();
    }
);

$f3->run();