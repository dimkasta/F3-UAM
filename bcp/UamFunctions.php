<?php

/**
 * User Management class for the PHP Fat-Free Framework
 * The contents of this file are subject to the terms of the GNU General
 * Public License Version 3.0. You may not use this file except in
 * compliance with the license. Any of the license terms and conditions
 * can be waived if you get permission from the copyright holder.
 * Copyright (c) 2015 by dimkasta
 * Dimitris Kastaniotis <dimkasta@yahoo.gr>
 * @version 0.2.1.alpha
 * Requires PHP 5.5
 **/
class Uamfunctions
{
    public static function initialize()
    {
        $f3 = \Base::instance();
        //Setting the global DB object to something UAM knows
        $f3->set("uamDb", $f3->get($f3->uamDbObjectName));

        //Create tables if fluidmode is on and schema does not exist
        $tables = $f3->uamDb->exec("SELECT COUNT(TABLE_NAME) as countTables FROM information_schema.tables WHERE TABLE_SCHEMA = '" . $f3->uamDb->name() . "' AND TABLE_NAME = 'users'");

        if(empty($f3->uamFluidMode))
        {
            $f3->uamFluidMode = false;
        }
        if ($f3->uamFluidMode === true && $tables[0]["countTables"]  < 1) {
            \Uamfunctions::createTables();
        }

        //Preparing the User Mapper
        $f3->set('userMapper', new \DB\SQL\Mapper($f3->uamDb, 'Users'));

        //Preloading all roles
        $f3->set('roleMapper', new \DB\SQL\Mapper($f3->uamDb, 'Roles'));
        $uamRoles = [];
        $roles = $f3->roleMapper->find();
        foreach ($roles as $role) {
            $uamRoles[$role->ID] = $role->role;
        }
        $f3->set('uamRoles', $uamRoles);

        //Preparing the userRoles mapper
        $f3->set('userRoleMapper', new \DB\SQL\Mapper($f3->uamDb, 'UserRoles'));
    }

    public static function createTables()
    {
        $f3 = \Base::instance();
        $f3->uamDb->exec("CREATE TABLE IF NOT EXISTS Users (
			  ID int(11) NOT NULL AUTO_INCREMENT,
			  username varchar(20) NOT NULL,
			  email varchar(50) NOT NULL,
			  isVerified tinyint(1) NOT NULL,
			  verificationtoken varchar(100) NOT NULL,
			  tokendate datetime NOT NULL,
			  isActive tinyint(1) NOT NULL,
			  password varchar(200) NOT NULL,
			  newEmail varchar(100) NOT NULL,
			  newPassword varchar(100) NOT NULL,
			  PRIMARY KEY (ID)
			)");
        //To make sure admin ID is random
        $f3->uamDb->exec("ALTER TABLE Users AUTO_INCREMENT=" . rand(1000,10000));

        $f3->set('userMapper', new \DB\SQL\Mapper($f3->uamDb, 'Users'));
        //TODO: Seed ID into a random number to avoid admin always having 1 as ID
        $f3->userMapper->reset();
        $f3->userMapper->username = 'administrator';
        $f3->userMapper->email = $f3->uamEmail;
        $f3->userMapper->isVerified = 1;
        $f3->userMapper->isActive = 1;
        $f3->userMapper->password = '$2y$10$veUGc0BKWWQxCZAYtIcT9.x3M.xNFPKgVFALKPH9HNnnVCMPscZ3a';
        $f3->userMapper->save();
        $f3->userMapper->reset();

        $f3->uamDb->exec("
          CREATE TABLE IF NOT EXISTS Roles (
            ID int(11) NOT NULL AUTO_INCREMENT,
            role varchar(20) NOT NULL,
            PRIMARY KEY (ID)
          )
        ");

        $f3->uamDb->exec("
            INSERT INTO Roles (role) VALUES ('Administrator')
        ");

        $f3->uamDb->exec("
            CREATE TABLE IF NOT EXISTS UserRoles (
              ID int(11) NOT NULL AUTO_INCREMENT,
              user_id int(11) NOT NULL,
              role_id int(11) NOT NULL,
              PRIMARY KEY (ID),
              FOREIGN KEY (user_id) REFERENCES Users(ID) ON DELETE CASCADE,
              FOREIGN KEY (role_id) REFERENCES Roles(ID) ON DELETE CASCADE
            )
        ");

        $f3->uamDb->exec("
            INSERT INTO UserRoles (role_id, user_id) VALUES (1, (SELECT MAX(ID) FROM Users))
        ");

        $f3->uamDb->exec("
            CREATE TABLE IF NOT EXISTS ProfileFields (
              ID int(11) NOT NULL AUTO_INCREMENT,
              fieldname varchar(20) NOT NULL,
              fieldorder int(11) NOT NULL,
              mandatory tinyint(1),
              PRIMARY KEY (ID)
            )
        ");

        $f3->uamDb->exec("
            INSERT INTO ProfileFields (fieldname, fieldorder, mandatory) VALUES ('First Name', 1, 1)
        ");
        $f3->uamDb->exec("
            INSERT INTO ProfileFields (fieldname, fieldorder, mandatory) VALUES ('Last Name', 2, 0)
        ");
        $f3->uamDb->exec("
            INSERT INTO ProfileFields (fieldname, fieldorder, mandatory) VALUES ('Company', 3, 0)
        ");

        $f3->uamDb->exec("
            CREATE TABLE IF NOT EXISTS UserProfileFields (
              ID int(11) NOT NULL AUTO_INCREMENT,
              field_id int(11) NOT NULL,
              user_id int(11) NOT NULL,
              field_value varchar(100) NOT NULL,
              PRIMARY KEY (ID),
              FOREIGN KEY (field_id) REFERENCES ProfileFields(ID) ON DELETE CASCADE,
              FOREIGN KEY (user_id) REFERENCES Users(ID) ON DELETE CASCADE
            )
        ");

        $f3->uamDb->exec("
            INSERT INTO UserProfileFields (field_id, field_value, user_id) VALUES (1, 'Awesome Admin', (SELECT MAX(ID) FROM Users))
        ");
        $f3->uamDb->exec("
            INSERT INTO UserProfileFields (field_id, field_value, user_id) VALUES (3, 'Iconic LTD', (SELECT MAX(ID) FROM Users))
        ");
        //TODO: Enhancement Add FKs


    }

    //Validates user and email, Stores the user data, creates a validation token and emails it
    public static function doSubscription($username, $password, $email)
    {
        //TODO: Multilingual text
        $f3 = \Base::instance();

        //Validate entries
        $usernameValid = \Uamfunctions::usernameAvailable($username);
        $emailValid = \Uamfunctions::emailAvailable($email);
        $passwordValid = strlen($password) >= 8;

        //TODO: This below is not very clear
        $json = \RESTAPI::getObject();
        $json->errors->email = $emailValid->errors->email;
        $json->errors->username = $usernameValid->errors->username;
        $json->messages->email = $emailValid->messages->email;
        $json->messages->username = $usernameValid->messages->username;
        if ($passwordValid) {
            $json->messages->password = "Password is valid";
        } else {
            $json->messages->password = "Password is not valid";
        }

        $f3->userMapper->load(array('username=? OR email=?', $username, $email));
        if ($usernameValid->success && $emailValid->success && $passwordValid && $f3->userMapper->dry()) {
            $f3->userMapper->username = $username;
            $f3->userMapper->email = $email;
            $d = new \DateTime('NOW');
            $f3->userMapper->tokendate = $d->format(\DateTime::ISO8601);
            $f3->userMapper->verificationtoken = password_hash($email, PASSWORD_DEFAULT);
            $f3->userMapper->password = password_hash($password, PASSWORD_DEFAULT);
            $f3->userMapper->save();
            \UamEmail::sendValidationTokenEmail($f3->userMapper->email, $f3->userMapper->verificationtoken, "Create an Account");
            $json->success = true;
            //TODO: Remove form messages ?
            $json->messages->form = "Sign Up successful. Please check your email for the verifification email link";
        } else {
            $json->success = false;
            $json->errors->form = "Sign Up unsuccessful";
        }
        return $json;
    }

    public static function usernameAvailable($username)
    {
        $json = \RESTAPI::getObject();
        if ($username === 'guest') {
            $json->success = false;
            $json->errors->username = "Username cannot be guest";
        } else {
            $f3 = \Base::instance();
            $f3->userMapper->load(array('username=?', $username));
            if ($f3->userMapper->dry()) {
                $json->success = true;
                $json->errors->username = "Username is available";
            } else {
                $json->success = false;
                $json->errors->username = "Username is already in use.";
            }
        }
        return $json;
    }

    //Verify that email does not exist and that MX entries exist. Nice for Ajax GET validation
    public static function emailAvailable($newemail)
    {
        $rest = \RESTAPI::getObject();
        $audit = \Audit::instance();
        $f3 = \Base::instance();

        $emailValid = $audit->email($newemail, TRUE);

        //Check if email is already registered
        $f3->userMapper->load(array('email=?', $newemail));
        $emailAvailable = $f3->userMapper->dry();

        if ($emailValid && $emailAvailable) {
            $rest->success = true;
            $rest->messages->email = "Email is valid and available";
        } else {
            $rest->success = false;
            if (!$emailAvailable) {
                $rest->errors->email = "Email is already in use";
            }
            if (!$emailValid) {
                $rest->errors->email = "Email is not valid";
            }
        }
        return $rest;
    }

    public static function doLogin($username, $password)
    {
        $f3 = \Base::instance();
        $f3->userMapper->load(array('username=? AND isVerified = 1 AND isActive = 1', $username));

        $json = \RESTAPI::getObject();

        $userExistsAndIsActive = !$f3->userMapper->dry();
        $passWordIsValid = password_verify($password, $f3->userMapper->password);

        if ($userExistsAndIsActive && $passWordIsValid) {
            $user = $f3->get("SESSION.uamUser");
            $user->username = $f3->userMapper->username;
            $user->email = $f3->userMapper->email;
            $user->gravatar = \Uamfunctions::getGravatar($user->email, 80);
            $user->ID = $f3->userMapper->ID;

            $json->success = true;
        } else {
            $json->success = false;
        }
        return $json;
    }

    public static function getRoles() {
        $f3 = \Base::instance();
        //TODO: Eliminate the additional query. save teh ID somewhere
        $f3->userMapper->load(array('username=?', $f3->get('SESSION.uamUser')->username));
        $roles = $f3->userRoleMapper->find(array('user_id=?', $f3->userMapper->ID));

        $roleIds = [];
        foreach ($roles as $role)
        {
            array_push($roleIds, $role->role_id);
        }

        return $roleIds;
    }

    //Used to check if the user has a role
    public static function isInRole($role_id)
    {
        $f3 = \Base::instance();
        return in_array($role_id, $f3->get('SESSION.uamUser')->roles);
    }

    public static function getGravatar($email, $size) {
        return "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=mm&s=" . $size;
    }

    public static function getAll() {
        $f3 = \Base::instance();
        return $f3->userMapper->find('ID>0', array( 'order'=>'username'));
    }

    public static function updateRole() {
        $f3 = \Base::instance();

        $id = $f3->get('POST.roleId');
        $newRoleName = $f3->get('POST.roleName');

        echo $id . "-" . $newRoleName;

        $f3->roleMapper->load(array('ID = ?', $id));
        if($f3->roleMapper->dry()) {
            //echo "nope";
            return false;
        }
        else {
            $f3->roleMapper->role = $newRoleName;
            $f3->roleMapper->save();
            //echo "yep";
            return true;
        }

    }

    public static function deleteRole() {
        $f3 = \Base::instance();

        $id = $f3->get('POST.roleId');

        if($id == 1){
            return false;
        }
        else {
            $f3->roleMapper->load(array('ID = ?', $id));
            $f3->roleMapper->erase();
        }
    }

    public static function newRole() {
        $f3 = \Base::instance();

        $name = $f3->get('POST.newRoleName');
        $f3->roleMapper->reset();
        $f3->roleMapper->role = $name;
        $f3->roleMapper->save();
        $f3->roleMapper->reset();
    }






//TODO: Everything below this needs review


    //Should be triggered by the emailverificationroute to verify the email link click and activate the account
    public static function validateEmail()
    {
        $f3 = \Base::instance();
//        $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
        $token = $f3->get('GET.token');
        $email = $f3->get('GET.email');
        $f3->userMapper->load(array('email=? AND isActive = 0 AND isVerified = 0', $f3->get('GET.email')));
        $now = new \DateTime('NOW');
        $tokendate = new \DateTime($f3->userMapper->tokendate);
        //check if verification code is old and resend email
        if (date_add($tokendate, date_interval_create_from_date_string('1 days')) < $now) {
            $f3->userMapper->verificationtoken = password_hash($f3->get('POST.email'), PASSWORD_DEFAULT);
            $f3->userMapper->tokendate = $now->format(\DateTime::ISO8601);
            $f3->userMapper->save();
            \WebUAM::sendValidationTokenEmail($f3->userMapper->email, $f3->userMapper->verificationtoken, "Create an Account");
            //TODO: Make this return an object
            $message = "The token was older than 1 day. We have sent you a fresh one. Please check your email and click the verification link";
            throw new Exception($message);
        } else {
            //verify email
            if (!($f3->userMapper->dry()) && $f3->userMapper->verificationtoken == $f3->get('GET.token')) {
                $f3->userMapper->isVerified = 1;
                $f3->userMapper->isActive = 1;
                $f3->userMapper->save();
                return true;
            } else {
                return false;
            }
        }

    }

    //Creates the verification token, stores the new email for reference and sends the validation email
    public static function requestChangeEmail($newEmail)
    {
        if (\WebUAM::emailAvailable($newEmail)) {
            $f3 = \Base::instance();
            $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
            $user->load(array('username=?', $f3->SESSION[$f3->sessionusername]));
            $options = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
            $user->verificationtoken = password_hash($newEmail, PASSWORD_BCRYPT, $options);
            $user->newvalue = $newEmail;
            $user->save();
            \WebUAM::sendValidationTokenEmail($newEmail, $user->verificationtoken, "Change your Email");
            return true;
        } else {
            return false;
        }
    }

    //Checks the token against the stored new email and stored token, and updates the email upon success
    public static function doChangeEmail()
    {
        $f3 = \Base::instance();
        $email = $f3->GET["email"];
        $token = $f3->GET["token"];
        $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
        $user->load(array('newvalue=?', $email));
        if (!($user->dry()) && password_verify($email, $user->verificationtoken) && $token === $user->verificationtoken) {
            $user->email = $email;
            $user->newvalue = "";
            $user->save();
            return true;
        } else {
            return false;
        }
    }

    //Creates the verification token, stores the new email for reference and sends the validation email
    public static function requestChangePassword($newPassword)
    {
        $f3 = \Base::instance();
        $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
        $user->load(array('username=?', $f3->SESSION[$f3->sessionusername]));
        $options = ['cost' => 5, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
        $user->verificationtoken = password_hash($user->email, PASSWORD_BCRYPT, $options);
        $passoptions = ['cost' => 11, 'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),];
        $user->newvalue = password_hash($newPassword, PASSWORD_BCRYPT, $passoptions);
        $user->save();
        \WebUAM::sendValidationTokenEmail($user->email, $user->verificationtoken, "Change your Password");
        return true;
    }

    //called from the clicked link to execute the password change.
    public static function doChangePassword()
    {
        $f3 = \Base::instance();
        $email = $f3->GET["email"];
        $token = $f3->GET["token"];
        $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
        $user->load(array('email=?', $email));
        if (!($user->dry()) && !(empty($user->newvalue)) && $token === $user->verificationtoken) {
            $user->password = $user->newvalue;
            $user->newvalue = "";
            $user->save();
            return true;
        } else {
            return false;
        }
    }





    //Used to deacctivate user account and so not allowing login
    public static function toggleAccountActivation($username)
    {
        $f3 = \Base::instance();
        $user = new \DB\SQL\Mapper($f3->get($f3->dbobject), 'Users');
        $user->load(array('username=?', $username));
        $user->isActive = !$user->isActive;
        $user->save();
        return $user->isActive;
    }
}
?>
