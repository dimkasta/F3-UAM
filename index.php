<?php
$f3=require('../../fatfree-master/lib/base.php');
$f3->set('DEBUG',3);
$f3->config('config.ini');
$f3->set('db',new \DB\SQL('mysql:host=' . $f3->dbHost . ';port=' . $f3->dbPort . ';dbname=' . $f3->dbName , $f3->dbUser , $f3->dbPassword));

$f3->route('GET /',
	function($f3) {



	}
);

$f3->run();