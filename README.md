# F3-UAM

A class standing somewhere between Model and Controller, abstracting good practice functionality around User Access Management in a Web Application based on F3

##Requirements

The class needs PHP 5.5 so that password hashing and validating works. If you are using an older PHP version, you should use the bcrypt stuff from F3
For now, the class only works with mySQL

##

##Setup

###Variables

First you have to setup some F3 variables, so that the class knows where you keep your objects. This way the class can work with your existing code elements.
A sample ini file is included (config.ini)

1. site: Holds your site name
2. domain: Holds your domain
3. email: Holds the email used to send the email validation links
4. emailverificationroute: Holds the route alias that executes the actual mail validation coming from the email link click.
5. sessionusername: Holds the name of the SESSION variable where you keep your user name
6. dbobject: Holds the name of the db connection that you are using in your code
7. dbhost: Is the mysql server (Usually localhost)
8. dbport: Is the mysql port used
9. dbname: Is the name of the database
10. dbuser: Is the name of the db user
11. dbpassword: Is the db password

###Files

You just have to copy WebUAM.php into your lib folder, or in your AUTOLOAD folder

##Usage

WebUAM extends \Prefab, so you can use the instance() call to get your singleton object

```
$f3->uam = \WebUAM::instance();
```

###DB Table Creation

To create the required table, use the static function call

```
\WebUAM::createUserTable();
```

This executes a create table SQL command and creates a Users table and includes the following fields

* ID int(11) NOT NULL AUTO_INCREMENT
*	username varchar(10) NOT NULL
*	email varchar(50) NOT NULL
*	isVerified tinyint(1) NOT NULL (used to check if the user has verified his email)
* verificationtoken varchar(100) NOT NULL (used to store the verification token emailed to the user)
*	tokendate datetime NOT NULL (used to store the token date so that the token expires after 1 day)
*	isActive tinyint(1) NOT NULL (used to allow admins or users to deactivate accounts by preventing login in)
*	password varchar(100) NOT NULL
*	newvalue varchar(100) NOT NULL (used to store new password hashes and new emails before they are verified with an email link click)
*	isAdmin tinyint(1) NOT NULL (used for simple role assignment 0 not implemented yet)
*	isAuthor tinyint(1) NOT NULL (used for simple role assignment 0 not implemented yet)
*	isEditor tinyint(4) NOT NULL (used for simple role assignment 0 not implemented yet)
* PRIMARY KEY (ID)

###Usual program flow

1. User opens the application - normal routing is used
2. Session is reset with a guest user name

```
$f3->uam->restartSession();
```

3. User clicks the Sign Up link. A Route is needed to display the form
4. User fills in his info and submits. AJAX calls can be made to new Routes that validate that user name and email are not already used and are valid

```
usernameAvailable($username);
emailAvailable($newemail);
```

5. The server receives the data in a new route that implements doSubscription($username, $email, $password). There email and username are revalidated, the password is hashed
