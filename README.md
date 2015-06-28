# F3-UAM v0.1.alpha

A plugin standing somewhere between Model and Controller, abstracting good practice functionality around User Access Management in a Web Application based on F3

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

You just have to copy WebUAM.php into your lib folder, or in your AUTOLOAD folder

##Usage

WebUAM extends \Prefab, so you can use the static instance() call to get your object

```PHP
$f3->uam = \WebUAM::instance();
```

###DB Table Creation

Table creation is automatic, if you set a configuration variable. I call it fluidmode to keep it in context with existing F3 experience (Cortex). Just remember to set it to false to save some processing time.

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
$f3->uam->restartSession("guest");
```

###User Signup
* User clicks the Sign Up link. A Route is needed to display the form
* User fills in his info and submits. AJAX calls can be made to new Routes that validate that user name and email are not already used and are valid. Email validation also checks for proper MX stuff.

```PHP
$test = $f3->uam->usernameAvailable("myusername");
if($test) {
    echo "username available<br />";
}
else {
    echo "username not available<br />";
}

$test3 = $f3->uam->emailAvailable("me@mydomain.com");
if($test3 ) {
    echo "email available<br />";
}
else {
    echo "email not available<br />";
}
```

* The server receives the data in a POST route that implements doSubscription($username, $email, $password). Email and username are revalidated, the password is hashed, and a verification token is created. The user is saved as inactive and unverified, and a verification link is emailed to the user.

```PHP
$test8 = $f3->uam->doSubscription("newusername", "me@mydomain.com", "12345678");
if($test8) {
    echo "ok subscribe<br />";
}
else {
    echo "not ok subscribe<br />";
} 
```

* The user receives an email with a validation link and clicks it. He is sent to a route that must implement the validateEmail() function. This must be the route defined in the config, so that the class knows what alias to include in the verification link. The token is checked and if it is found identical and less than 1 day has passed from its creation, then the user is switched to verified and active in the db.

```PHP
$test7 = $f3->uam->validateEmail();
if($test7){
    echo "ok mail validate<br />";
}
else {
    echo "not ok mail validate<br />";
}
```
You do not need to pass anything to the function. It gets everything it wants from GET.

###User Login
* User clicks the login link. A route is needed to show the form.
* A route receives the login POST and implements the dologin($username, $password) function

```PHP
$test13 = $f3->uam->doLogin('username', '12345678');
if($test13){
    echo "ok login<br />";
}
else {
    echo "not ok login<br />";			
}
```

###User changes email
* User clicks the change my email link. A route shows him the form.
* The server receives the change email POST in a route and implements requestChangeEmail("me@mydomain.com"). A new verification token is created and the new email is stored in newvalue. An email is sent to the user with a verification link.
* User clicks the email link.
* The server receives the GET verification request on the same configured route. If the newvalue is not empty, it should check if it contains an email. If yes, then execute doChangeEmail() which changes the email and resets the temp fields.

```PHP
$test10 = $f3->uam->doChangeEmail();
if($test10){
    echo "ok do change email";
}
else {
    echo "not ok do change email";
}
```

###User changes password
* User clicks the change my password link. A route shows him the form.
* The server receives the change password POST in a route that implements requestChangePassword('87654321'). A new verification token is created and the new pass is hased and stored in newvalue. An email is sent to the user with a verification link.
 
```PHP
$test11 = $f3->uam->requestChangePassword('123456');
if($test11){
    echo "ok req change pass<br />";
}
else {
    echo "not ok req change pass<br />";
}
```

* User clicks the email link.
* The server receives the GET verification request on the same configured route. If the newvalue is not empty, it should check if it contains an email. If not, then execute doChangePassword() which changes the password and resets the temp fields.

```PHP
$test12 = $f3->uam->doChangePassword();
if($test12){
    echo "ok req change pass<br />";
}
else {
    echo "not ok req change pass<br />";
}
```

###User logs out
* User clicks the Logout link. The route uses the restartSession($username) function to reset the SESSION and set the username to "guest"

```PHP
$f3->uam->doLogout();
```

###Simple role Management
The plugin contains simple Role management. Role State is stored in boolean (tinyint(1)) fields called isAdmin, isAuthor and isEditor. These 3 Roles were chosen as default because they fit the most common CMS practice. But you can easily add your own columns/Roles and access them. The relevant API works like this

```PHP
$test14 = $f3->uam->isAdmin('dimkasta');
if($test14){
    echo "is in role<br />";
}
else {
	echo "not in role<br />";
}
			
$test15 = $f3->uam->isEditor('dimkasta');
if($test15){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}
			
$test16 = $f3->uam->isAuthor('dimkasta');
if($test16){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}

$test17 = $f3->uam->toggleAdmin('dimkasta');
if($test17){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
			
$test18 = $f3->uam->toggleAuthor('dimkasta');
if($test18){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
			
$test19 = $f3->uam->toggleEditor('dimkasta');
if($test19){
	echo "ok role change on<br />";
}
else {
	echo "ok role change off<br />";
}
```

There are also functions that allow you to access your own Role columns. All you have to do is add them in the Users table with a type of tinyint(1), and use them as follows

```PHP
$test20 = $f3->uam->isInRole("myusername", "isNewRole");
if($test20){
	echo "is in role<br />";
}
else {
	echo "not in role<br />";
}

$test21 = $f3->uam->toggleRole("myusername", "isNewRole");
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
$test22 = $f3->uam->toggleAccountActivation('thisUserName');
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
$test23 = getGravatar($email);
echo '<img src="' . $test23 . '" />';
```

##ToDo
- [ ] Add demo site.
- [ ] Make calls more REST/JSON friendly
- [ ] Make role queries simpler
- [x] ~~Create a RESTAPI Helper (Can be used standalone)~~
- [x] ~~Account deactivation (for admin usage or user closing their own account)~~
- [ ] Make newvalue type check internal. Split it to two fields
- [x] ~~Do not allow guest as a username.~~
- [x] ~~Add a function to cache gravatar link.~~
- [x] ~~Implement basic Role Management.~~
- [ ] Add support for older PHP versions.
- [ ] Implement a mechanism to lock login attempts after 3 consecutive failed login attempts. And send an email to the user.
- [ ] Create templates for all routes.
- [ ] Create email templates.
- [ ] Create sample route ini file
- [x] ~~Implement doLogout().~~
- [ ] Add good practice suggestions like csrf check, throttling etc.

##About
This is a new plugin so treat it with caution. If you have any questions or corrections, feel free to contact me at dimkasta@yahoo.gr or at @dimkasta

Or post your thoughts here https://groups.google.com/forum/#!topic/f3-framework/3vq8M_5-KKo

http://www.autowebic.com

![by autowebic](http://autowebic.com/autowebic_trio.jpg)
