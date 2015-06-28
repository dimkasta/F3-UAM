<?php
//Sample Controller. You need routes for each one of the functions below
namespace Controller;
class Users {
	public static function subscribe($f3) {
    		//Show subscription form
	}
    
	public static function doSubscribe($f3) {
		\RESTAPI::returnJSON($f3->uam->doSubscription($f3->POST['username'], $f3->POST['email'], $f3->POST['password']);
	}
	
	public static function validateEmail($f3) {
		$f3 = \Base::instance();
		$f3->uam->validateEmail();
		$f3->reroute('/login');
	}
	
	public static function login() {
		//Show login form
	}
	
	public static function doLogin($f3) {
		\RESTAPI::returnJSON($f3->uam->doLogin($f3->POST['username'], $f3->POST['password']));
	}
	
	public static function logout($f3) {
		$f3->uam->doLogout();
		$f3->reroute('/');
	}
}
?>
