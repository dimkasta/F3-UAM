<?php
//TODO: Make this an installer
$f3=require('lib/base.php');
$f3->config('config.ini');
$f3->set('db',new \DB\SQL('mysql:host=' . $f3->dbHost . ';port=' . $f3->dbPort . ';dbname=' . $f3->dbName , $f3->dbUser , $f3->dbPassword));
$f3->set('DEBUG',3);

function addTest($testobject, $name, $test, $guidelines, $example, $found) {
    $testobject->expect(
        $test,
        '<strong>' . $name . '</strong><br>' . $guidelines . '<br>Example:<br>' . $example . '<br>Found: "' . $found . '"'
    );
}

$requirements = new Test();

$test = new Test();
echo "<H1>F3 User Access Management Installation</H1>";

addTest($requirements, 'Database Object', !empty($f3->uamDbObjectName), 'F3 UAM requires an existing Database object. Set uamDbObjectName in your configuration file to the name of the f3 database object of your application', 'uasmDbObjectName=db', $f3->uamDbObjectName);
addTest($requirements, "Fluid Mode", !empty($f3->uamFluidMode), 'Set in your configuration file to allow the library to create the tables. After that, set it to false to save processing time', 'uamFluidMode=true', $f3->uamFluidMode);

if(!empty($f3->uamDbObjectName) && !empty($f3->uamFluidMode)) {

    addTest($requirements, "Database Type", $f3->get($f3->get(uamDbObjectName))->driver() == "mysql", 'F3 UAM only supports mySQL', 'N/A', $f3->get($f3->get(uamDbObjectName))->driver());
    addTest($requirements, "Service Email", !empty($f3->uamEmail), 'Set your email in your configuration file to allow the library to create the administrator user and configure the email that will send the verification emails.', 'uamEmail=myemail@mydomain.com', $f3->uamEmail);
}

echo "<h2>Checking requirements and configuration</h2>";
foreach ($requirements->results() as $result) {
    echo $result['text'].' <strong>';
    if ($result['status'])
        echo '<br><span style="color:green;">OK</span></strong>';
    else
        echo '<br><span style="color:red;">Fail</span></strong> ('.$result['source'].')';
    echo '<br><br>';
}


    if($requirements->passed()) {
        new UamUser();
        $user = $f3->get("SESSION.uamUser");

        //TODO: Finish the rest of the installer

        $test->expect(
            !empty($user),
            'Created a user session'
        );

        $test->expect(
            $f3->uamRoles[1] == "Administrator",
            'Loaded admin role '
        );

        $user->login("administrator", "12345678");

        $test->expect(
            $user->username == "administrator",
            'User Logged in ' /*. JSON_ENCODE($f3->get('SESSION.user'))*/
        );

        $test->expect(
            $user->roles == [1],
            'Load User Roles'
        );

        $test->expect(
            $user->isInRole(0) === false,
            'User is not in Role 0'
        );

        $test->expect(
            $user->isAdmin() === true,
            'User is Admin'
        );

        $gravatar = $user->getGravatar($user->email, 80);

        $test->expect(
            $gravatar == "http://www.gravatar.com/avatar/7ef40ad5bab9c53123d75a9583175120?d=mm&s=80",
            "Getting Gravatar "
        );

        $isUser = $user->isUser();
        $test->expect(
            $isUser == true,
            "Is User "
        );

        $user->logout();

        $test->expect(
            $user->username == "guest",
            'User Logged Out ' /*. JSON_ENCODE($f3->get('SESSION.user'))*/
        );

        $isUser = $user->isUser();
        $test->expect(
            $isUser == false,
            "Is Guest "
        );

        $subscriptionResult = $user->subscribe("dimkasta", "12345678", "dimkasta@yahoo.com");

        $user->login("dimkasta", "12345678");

        $test->expect(
            $user->username == "guest",
            'User cannot login. Is still guest ' /*. JSON_ENCODE($f3->get('SESSION.user'))*/
        );

        \Uamfunctions::validateEmail();

        $user->login("dimkasta", "12345678");

        $test->expect(
            $user->username == "dimkasta",
            'User now can login ' /*. JSON_ENCODE($f3->get('SESSION.user'))*/
        );

        $test->expect(
            !empty($subscriptionResult),
            'User Subscription ' /*. JSON_ENCODE($subscriptionResult)*/
        );
        echo "<h2>Checking functionality</h2>";
        foreach ($test->results() as $result) {
            echo $result['text'].' <strong>';
            if ($result['status'])
                echo '<br><span style="color:green;">OK</span></strong>';
            else
                echo '<br><span style="color:red;">Fail</span></strong> ('.$result['source'].')';
            echo '<br><br>';
        }

    }


?>