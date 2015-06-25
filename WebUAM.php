<?php
/**
    User Management class for the PHP Fat-Free Framework
    The contents of this file are subject to the terms of the GNU General
    Public License Version 3.0. You may not use this file except in
    compliance with the license. Any of the license terms and conditions
    can be waived if you get permission from the copyright holder.
    Copyright (c) 2015 by dimkasta
    Dimitris Kastaniotis <dimkasta@yahoo.gr>
    @version 0.1.1
	Requires PHP 5.5
 **/
	
	class WebUAM extends \Prefab {
		public function __construct() {
		
		}
		
		//Call it statically to create the User table
		public static function createUserTable() {
			$f3 = \Base::instance();
			$uamdb = $f3->get($f3->dbobject);
			$uamdb->exec("CREATE TABLE IF NOT EXISTS Users (
			  ID int(11) NOT NULL AUTO_INCREMENT,
			  username varchar(10) NOT NULL,
			  email varchar(50) NOT NULL,
			  isVerified tinyint(1) NOT NULL,
			  verificationtoken varchar(100) NOT NULL,
			  tokendate datetime NOT NULL,
			  isActive tinyint(1) NOT NULL,
			  password varchar(100) NOT NULL,
			  newvalue varchar(100) NOT NULL,
			  isAdmin tinyint(1) NOT NULL,
			  isAuthor tinyint(1) NOT NULL,
			  isEditor tinyint(4) NOT NULL,
			  PRIMARY KEY (ID)
			)");
			return true;
		}

/* These belong in another class
		
		//Simple csrf validation. make sure that you use CSRF in your forms and requests
		public static function validateCSRF($csrf) {
			$f3 = \Base::instance();
			return $f3->SESSION['csrf'] === $csrf;
		}
		
		public static function createCSRF() {
			$options = ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
			$newToken = password_hash(date('NOW'), PASSWORD_BCRYPT, $options);
			$f3->SESSION['csrf'] = $newToken; // use this in hidden form fields
		}
*/
				
		//Clearing the SESSION and resetting username and csrf token
		public function restartSession($username) {
			$f3 = \Base::instance();
			$f3->clear("SESSION");
			$f3->SESSION[$f3->sessionusername]= $username;
			
			return $f3->SESSION;
		}
		
		//Verify that username does not exist. Nice for Ajax GET validation
		public function usernameAvailable($username) {
			$f3 = \Base::instance();
			$user = new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('username=?',$username));
			
			return $user->dry();
		}
		
		//Verify that email does not exist and that MX entries exist. Nice for Ajax GET validation
		public function emailAvailable($newemail) {
			$audit = \Audit::instance();
			$valid = $audit->email($newemail, TRUE);
			
			$f3 = \Base::instance();
			$user = new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('email=?',$newemail));
			
			return $user->dry() && $valid;
		}
		
		//Revalidates user and email, Stores the user data, creates a validation token and emails it
		public function doSubscription($username, $email, $password) {
			$usernameValid = $this->usernameAvailable($username);
			$emailValid = $this->emailAvailable($email);
			
			$f3 = \Base::instance();
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('username=? OR email=?',$username, $email));
			
			if( $usernameValid && $emailValid && $user->dry()) {
				$user->username = $username;
				$user->email = $email;
				
				$tokenoptions = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
				$user->verificationtoken = password_hash($email, PASSWORD_BCRYPT, $tokenoptions);
				
				$d = new \DateTime('NOW');
				$user->tokendate = $d->format(\DateTime::ISO8601);
				
				$passoptions = ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
				$user->password = password_hash($password, PASSWORD_BCRYPT, $passoptions );
				
				$user->save();
				
				$this->sendValidationTokenEmail($user->email, $user->verificationtoken, "Create an Account");
				
/*				
				$to = $user->email;
				$subject = $f3->site . " Account Verificaton";
$txt = "You received this email because you have requested the creation of an Account at ". $f3->site . "\n Please click the link below to verify it\nhttp://" . $f3->domain . "/" . $f3->emailverificationroute . "?email=" . $user->email . "&token=" . $user->verificationtoken;
				$headers = "From: " . $f3->email;
					
				mail($to,$subject,$txt,$headers);
*/
				return true;
			}
			else {
				return false;
			}
		}
		
		//Should be triggered by the emailverificationroute to verify the email link click and activate the account
		public function validateEmail() {
			$f3 = \Base::instance();
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			
			$token = $f3->get('GET.token');
			$email = $f3->get('GET.email');
			
			$user->load(array('email=? AND isActive = 0 AND isVerified = 0',$f3->get('GET.email')));
			
			$now = new \DateTime('NOW');
			$tokendate = new \DateTime($user->tokendate);
			
			//check if verification code is old and resend email
			if(date_add($tokendate , date_interval_create_from_date_string('1 days')) < $now) {
				
				$options = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
				$user->verificationtoken = password_hash($f3->get('POST.email'), PASSWORD_BCRYPT, $options);
				$user->tokendate = $now->format(\DateTime::ISO8601);
				$user->save();
				
				$this->sendValidationTokenEmail($user->email, $user->verificationtoken, "Create an Account");

				$message = "The token was older than 1 day. We have sent you a fresh one. Please check your email and click the verification link";
				throw new Exception($message);
			}
			else {
				//verify email
				if(!($user->dry()) && $user->verificationtoken == $f3->get('GET.token'))
				{
					$user->isVerified = 1;
					$user->isActive= 1;
					$user->save();
					return true;
				}
				else {
					return false;
				}
			}
			
		}
		
		public function doLogin($username, $password) {
			$f3 = \Base::instance();
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('username=? AND isVerified = 1 AND isActive = 1',$username));
			
			//return password_verify($password, $user->password);
			
			if(!($user->dry()) && password_verify($password, $user->password))
			{
				$this->restartSession($user->username);
				return true;
			}
			else {
				return false;
			}
			
		}
		
		//Creates the verification token, stores the new email for reference and sends the validation email
		public function requestChangeEmail($newEmail) {
		
			if($this->emailAvailable($newEmail)) {
				$f3 = \Base::instance();
				$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
				$user->load(array('username=?',$f3->SESSION[$f3->sessionusername]));
				
				$options = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
				$user->verificationtoken = password_hash($newEmail, PASSWORD_BCRYPT, $options);
				$user->newvalue = $newEmail;
				$user->save();
				
				$this->sendValidationTokenEmail($newEmail, $user->verificationtoken, "Change your Email");
				return true;
			}
			else {
				return false;
			}
			
		}
		
		//Checks the token against the stored new email and stored token, and updates the email upon success
		public function doChangeEmail() {
			$f3 = \Base::instance();
			$email = $f3->GET["email"];
			$token = $f3->GET["token"];
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('newvalue=?',$email));
			
			if(!($user->dry()) && password_verify($email, $user->verificationtoken) && $token === $user->verificationtoken) {
				$user->email = $email;
				$user->newvalue = "";
				$user->save();
				return true;
			}
			else {
				return false;
			}
		}
		
		//Creates the verification token, stores the new email for reference and sends the validation email
		public function requestChangePassword($newPassword) {
			$f3 = \Base::instance();
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('username=?',$f3->SESSION[$f3->sessionusername]));
			
			$options = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
			$user->verificationtoken = password_hash($user->email, PASSWORD_BCRYPT, $options);
			
			$passoptions = ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
			$user->newvalue = password_hash($newPassword, PASSWORD_BCRYPT, $passoptions);
			
			$user->save();
				
			$this->sendValidationTokenEmail($user->email, $user->verificationtoken, "Change your Password");
			return true;
		}
		
		//called from the clicked link to execute the password change.
		public function doChangePassword() {
			$f3 = \Base::instance();
			$email = $f3->GET["email"];
			$token = $f3->GET["token"];
			$user=new \DB\SQL\Mapper($f3->get($f3->dbobject),'Users');
			$user->load(array('email=?',$email));
			
			if(!($user->dry()) && !(empty($user->newvalue)) && $token === $user->verificationtoken) {
				$user->password = $user->newvalue;
				$user->newvalue = "";
				$user->save();
				return true;
			}
			else {
				return false;
			}
		}
		
		//Called to send the validation token
		public function sendValidationTokenEmail($email, $token, $message) {
			$f3 = \Base::instance();
				$subject = $f3->site . " - Email Verificaton";
$txt = "You received this email because you have requested to " . $message . " at ". $f3->site . "\n Please click the link below to verify it\nhttp://" . $f3->domain . "/" . $f3->emailverificationroute . "?email=" . $email . "&token=" . $token;
				$headers = "From: " . $f3->email;
				//mail($email,$subject,$txt,$headers);
		}

		

	}
 
 ?>