<?php
//Sample Controller. You need routes for each one of the functions below
namespace Controller;
class Users {
	public static function subscribe($f3) {
    		//Show subscription form
	}
    
	public static function doSubscribe($f3) {
		\RESTAPI::returnJSON(\WebUAM::doSubscription($f3->POST['username'], $f3->POST['email'], $f3->POST['password']));
	}
	
	public static function validateEmail($f3) {
		\WebUAM::validateEmail();
		$f3->reroute('/login');
	}
	
	public static function login($f3) {
		//Show the login form
	}
	
	public static function doLogin($f3) {
		\RESTAPI::returnJSON(\WebUAM::doLogin($f3->POST['username'], $f3->POST['password']));
	}
	
	public static function logout($f3) {
		\WebUAM::doLogout();
		$f3->reroute('/');
	}
}
?>
