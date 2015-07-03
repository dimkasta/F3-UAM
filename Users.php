<?php
//Sample Controller. You need routes for each one of the functions below
namespace Controller;
class Users {
	public static function subscribe($f3) {
    		//Show subscription form
	}
    
	public static function doSubscribe($f3) {
		$params = json_decode(file_get_contents('php://input'));
		\RESTAPI::returnJSON(\WebUAM::doSubscription($params->username, $params->email, $params->password));
	}
	
	public static function validateEmail($f3) {
		\WebUAM::validateEmail();
		$f3->reroute('/login');
	}
	
	public static function login() {
		\FatTemplate::showContent('samples/login.htm');
	}
	
	public static function doLogin($f3) {
		$params = json_decode(file_get_contents('php://input'));
		\RESTAPI::returnJSON(\WebUAM::doLogin($params->username, $params->password));
	}
	
	public static function logout($f3) {
		\WebUAM::doLogout();
		$f3->reroute('/');
	}
}
?>
