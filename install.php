<?php
//TODO: Make this an installer
$f3=require('../../fatfree-master/lib/base.php');
$f3->config('config.ini');
$f3->set('db',new \DB\SQL('mysql:host=' . $f3->dbHost . ';port=' . $f3->dbPort . ';dbname=' . $f3->dbName , $f3->dbUser , $f3->dbPassword));
$f3->set('DEBUG',3);

$test = new Test();
echo "<H1>F3 User Access Management Installation</H1>";

//TODO: Put these in a separate test
$test->expect(
    !empty($f3->uamDbObjectName),
    'Database Object: Set uamDbObjectName in your configuration file to the name of the f3 database object of your application <br>(Found ' . $f3->uamDbObjectName . ')'
);

$test->expect(
    !empty($f3->uamFluidMode),
    'Fluid mode: Set uamFluidMode=true in your configuration file to allow the library create the tables. After that, set it to false to save '
);

if(!empty($f3->uamDbObjectName) && !empty($f3->uamFluidMode)) {
    $test->expect(
        $f3->get($f3->get(uamDbObjectName))->driver() == "mysql",
        'Database Driver: F3 UAM supports mySQL <br>(Found ' . $f3->get($f3->get(uamDbObjectName))->driver() . ')'
    );




    new UamUser();
    $user = $f3->get("SESSION.uamUser");

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
    $test->expect(
        !empty($subscriptionResult),
        'User Subscription ' /*. JSON_ENCODE($subscriptionResult)*/
    );
}


foreach ($test->results() as $result) {
    echo $result['text'].' <strong>';
    if ($result['status'])
        echo '<br><span style="color:green;">Pass</span></strong>';
    else
        echo '<br><span style="color:red;">Fail</span></strong> ('.$result['source'].')';
    echo '<br><br>';
}
?>