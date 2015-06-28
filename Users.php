<?php
//Sample Controller. You need routes for each one of the functions below

namespace Controller;

class Users {

	public static function subscribe($f3) {
    		//Show the subscription form
	}
    
  //You can call this with AJAX or internally
	public static function doSubscribe() {
		$f3 = \Base::instance();
		$params = json_decode(file_get_contents('php://input'));
		$json = $f3->uam->doSubscription($params->username, $params->email, $params->password);
    		\RESTAPI::returnJSON($json);
	}
	
	//You can call this with AJAX or internally
	public static function validateEmail() {
		$f3 = \Base::instance();
		$f3->uam->validateEmail();
		$f3->reroute('/login');
	}
	
	
	public static function login() {
		//Show the login form
	}
	
	//You can call this with AJAX or internally
	public static function doLogin() {
		$f3 = \Base::instance();
		$params = json_decode(file_get_contents('php://input'));
		$json = $f3->uam->doLogin($params->username, $params->password);
		\RESTAPI::returnJSON($json);
	}
	
	
	public static function logout() {
		$f3 = \Base::instance();
		$f3->uam->doLogout();
		$f3->reroute('/');
	}
}
?>
