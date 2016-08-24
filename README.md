# F3-UAM v0.2.1.alpha

UPDATE (8/2016): Development is active once more. 

**DO NOT use over your old code. The Current code is not compatible with the old**

**Documentation also requires heavy changes**

If you used the library before, a lot of things are changed.

If you need any features now would be the time to ask for them with an Issue


F3 UAM is a plugin standing somewhere between Model and Controller, abstracting good practice functionality around User Access Management in a Web Application based on F3

For a complete list of pending functionality check the link below

https://github.com/dimkasta/F3-UAM/issues



Please keep in mind that this is a work in progress and should be used with care.

##Table of Contents

* [Requirements](#requirements)
* [Setup and Basic usage](#setup)
* [DB Table Creation](#db-table-creation)
* [Usual program flow](#usual-program-flow)
  * [User Signup](#user-signup)
  * [User Login](#user-login)
  * [User changes email](#user-changes-email)
  * [User changes password](#user-changes-password)
  * [User logs out](#user-logs-out)
  * [Simple role Management](#simple-role-management)
  * [Account Activation-Deactivation](#account-activation-deactivation)
  * [Get Gravatar image](#get-gravatar-image)
* [ToDo](#todo)
* [About](#about)


##Requirements

The class needs PHP 5.5 so that password hashing and validating works. If you are using an older PHP version, you should use the bcrypt stuff from F3. Support for older PHP will be added very soon. For now, the class only works with mySQL.

##Setup

###Variables

First you have to setup some F3 variables, so that the class knows where you keep your objects. This way the class can work with your existing code elements.
A sample ini file is included (config.ini)

1. **site**: Holds your site name (Used in the validation email)
2. **domain**: Holds your domain (Used in the validation email)
3. **email**: Holds the email used to send the email validation links
4. **emailverificationroute**: Holds the route alias that executes the actual mail validation coming from the email link click.
5. **sessionusername**: Holds the name of the SESSION variable where you keep your user name (Used for login)
6. **dbobject**: Holds the name of the db connection that you are using in your code
7. **dbhost**: Is the mysql server (Usually localhost) (Used for table creation)
8. **dbport**: Is the mysql port used (Used for table creation)
9. **dbname**: Is the name of the database (Used for table creation)
10. **dbuser**: Is the name of the db user (Used for table creation)
11. **dbpassword**: Is the db password (Used for table creation)

###Files

You just have to copy WebUAM.php into your lib folder, or in your AUTOLOAD folder.
You also need RESTAPI.php in the same folder, which is just a small helper class to abstract some repeating code.

##Usage

All WebUAM functions are now static so there is no need for extra initialization code in your index.php.

###DB Table Creation

Table creation is automatic. You just have to set a configuration variable. I call it fluidmode to keep it in context with existing F3 experience (Cortex). Just remember to set it to false after the table is created, to save some processing time.

```PHP
fluidmode=true
```

This creates a Users table and includes the following fields

* **ID int(11) NOT NULL AUTO_INCREMENT**
* **username varchar(10) NOT NULL**
* **email varchar(50) NOT NULL**
* **isVerified tinyint(1) NOT NULL** (used to check if the user has verified his email)
* **verificationtoken varchar(100) NOT NULL** (used to store the verification token emailed to the user)
* **tokendate datetime NOT NULL** (used to store the token date so that the token expires after 1 day)
* **isActive tinyint(1) NOT NULL** (used to allow admins or users to deactivate accounts by preventing login in)
* **password varchar(100) NOT NULL**
* **newvalue varchar(100) NOT NULL** (used to store new password hashes and new emails before they are verified with an email link click)
* **isAdmin tinyint(1) NOT NULL** (used for simple role assignment 0 not implemented yet)
* **isAuthor tinyint(1) NOT NULL** (used for simple role assignment 0 not implemented yet)
* **isEditor tinyint(4) NOT NULL** (used for simple role assignment 0 not implemented yet)
* **PRIMARY KEY (ID)**

##Usual program flow

* User opens the application - normal routing is used
* Session is reset with a guest user name

```PHP
\WebUAM::startSession();
```
This is also the call that checks if the fluidmode is true to create the tables.

###User Signup
* User clicks the Sign Up link. A Route shows the form
* User fills in his info and submits. AJAX calls can be made to validate that the user name and email are valid and not already in use. Email validation also checks for proper MX stuff.
* The functions return a json object that includes detailed messages and errors

```PHP
$test = \WebUAM::usernameAvailable("myusername");
if($test->success) {
    echo $test->messages->username;
}
else {
    echo $test->errors->username;
}

$test3 = \WebUAM::emailAvailable("me@mydomain.com");
if($test3->success ) {
    echo $test3->messages->email;
}
else {
    echo $test->errors->email;
}
```

* The server receives the data in a POST route that implements doSubscription($username, $email, $password). Email and username are revalidated, the password is hashed, and a verification token is created. The user is saved as inactive and unverified, and a verification link is emailed to the user.

```PHP
$test8 = \WebUAM::doSubscription("newusername", "me@mydomain.com", "12345678");
if($test8->success) {
    echo $test8->messages->form;
}
else {
    echo $test8->errors->form . "<br />" . $test8->errors->email . "<br />" . $test8->errors->username . "<br />" . $test8->errors->password . "<br />" ;
} 
```

* The user receives an email with a validation link and clicks it. He is sent to a route that must implement the validateEmail() function. This must be the route defined in the config, so that the class knows what alias to include in the verification link. The token is checked and if it is found identical and less than 1 day has passed from its creation, then the user is switched to verified and active in the db.

```PHP
$test7 = \WebUAM::validateEmail();
if($test7){
    echo "ok mail validate<br />";
}
else {
    echo "not ok mail validate<br />";
}
```
You do not need to pass anything to the function. It gets everything it wants from GET.

###User Login
* User clicks the login link. The route shows the form.
* A route receives the login POST and implements the dologin($username, $password) function
* The function returns a JSON object with messages

```PHP
$test13 = \WebUAM::doLogin('username', '12345678');
if($test13->success){
    echo $test13->messages->form;
}
else {
    echo $test13->errors->form;			
}
```

###User changes email
* User clicks the change my email link. The route shows the form.
* The server receives the change email POST in a route and implements requestChangeEmail("me@mydomain.com"). A new verification token is created and the new email is stored. An email is sent to the user with a verification link.
* User clicks the email link.
* The server receives the GET verification request on the same configured routeand executes doChangeEmail() which changes the email and resets the temp fields.

```PHP
$test10 = \WebUAM::doChangeEmail();
if($test10){
    echo "ok do change email";
}
else {
    echo "not ok do change email";
}
```

###User changes password
* User clicks the change my password link. A route shows the form.
* The server receives the change password POST in a route that implements requestChangePassword('87654321'). A new verification token is created and the new pass is hashed and stored. An email is sent to the user with a verification link.
 
```PHP
$test11 = \WebUAM::requestChangePassword('123456');
if($test11){
    echo "ok req change pass<br />";
}
else {
    echo "not ok req change pass<br />";
}
```

* User clicks the email link.
* The server receives the GET verification request on the same configured route. If the newvalue is not empty, it executes doChangePassword() which changes the password and resets the temp fields.

```PHP
$test12 = \WebUAM::doChangePassword();
if($test12){
    echo "ok req change pass<br />";
}
else {
    echo "not ok req change pass<br />";
}
```

###User logs out
* User clicks the Logout link. The route uses the doLogout($username) function to reset the SESSION and set the username to "guest"

```PHP
\WebUAM::doLogout();
```

###Simple role Management
The plugin contains simple Role management. Role State is stored in boolean (tinyint(1)) fields called isAdmin, isAuthor and isEditor. These 3 Roles were chosen as default because they fit the most common CMS practice. But you can easily add your own columns/Roles and access them. The relevant API works like this

```PHP
$test14 = \WebUAM::isAdmin('dimkasta');
if($test14){
    echo "is in role<br />";
}
else {
	echo "not in role<br />";
}
			
$test15 = \WebUAM::isEditor('dimkasta');
if($test15){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}
			
$test16 = \WebUAM::isAuthor('dimkasta');
if($test16){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}

$test17 = \WebUAM::toggleAdmin('dimkasta');
if($test17){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
			
$test18 = \WebUAM::toggleAuthor('dimkasta');
if($test18){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
			
$test19 = \WebUAM::toggleEditor('dimkasta');
if($test19){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
```

There are also functions that allow you to access your own Role columns. All you have to do is add them in the Users table with a type of tinyint(1), and use them as follows

```PHP
$test20 = \WebUAM::isInRole("myusername", "isNewRole");
if($test20){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}

$test21 = \WebUAM::toggleRole("myusername", "isNewRole");
if($test21){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
```

###Account Activation-Deactivation
To be used in a simple route after a confirmation message

```
$test22 = \WebUAM::toggleAccountActivation('thisUserName');
if($test22){
	echo "account is active<br />";
}
else {
	echo "account is not active<br />";
}
```

##Get Gravatar Image
You can use this to get the Gravatar image url
The login function calls it automatically after success and stores it into SESSION['gravatar'].

```
$test23 = \WebUAM::getGravatar($email);
echo '<img src="' . $test23 . '" />';
```

##ToDo

[Issue Tracking](https://github.com/dimkasta/F3-UAM/issues)

##About
This is a new plugin so treat it with caution. If you have any questions or corrections, feel free to contact me at dimkasta@yahoo.gr or at @dimkasta

Or post your thoughts here https://groups.google.com/forum/#!topic/f3-framework/3vq8M_5-KKo

http://www.autowebic.com

![by autowebic](http://autowebic.com/autowebic_trio.jpg)
