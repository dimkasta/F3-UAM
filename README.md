# F3-UAM

A class standing somewhere between Model and Controller, abstracting good practice functionality around User Access Management in a Web Application based on F3

##Requirements

The class needs PHP 5.5 so that the password hashing and validating work. If you are using an older version, you can use the bcrypt stuff from F3
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
