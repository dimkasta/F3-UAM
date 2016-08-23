<?php

$f3=require('lib/base.php');

$test = new Test();

include('src/User.php');

new User();
$user = $f3->get("SESSION.user");

$test->expect(
    is_callable(array($user, 'login'), false, $callablename),
    'login is a function as ' . $callablename
);

$test->expect(
    is_callable(array($user, 'getRoles'), false, $callablename),
    'getRoles is a function as ' . $callablename
);

$test->expect(
    is_callable(array($user, 'logout'), false, $callablename),
    'logout() is a function as ' . $callablename
);

$test->expect(
    is_callable(array($user, 'getGravatar'), false, $callablename),
    'getGravatar() is a function as ' . $callablename
);




$test->expect(
    !empty($user),
     'Created a user session'
);

$user->login("dimkasta", "12345678");

//$user2 = $f3->get("SESSION.user");

$test->expect(
    $user->username == "dimkasta",
    'User Logged in'
);

$test->expect(
    $user->roles == [2],
    'User Roles'
);

$gravatar = $user->getGravatar($user->email, 80);

$test->expect(
    $gravatar == "http://www.gravatar.com/avatar/7ef40ad5bab9c53123d75a9583175120?d=mm&s=80",
    "Getting Gravatar "
);

$user->logout();

//$user3 = $f3->get("SESSION.user");

$test->expect(
    $user->username == "guest",
    'User logged out'
);



//        echo JSON_ENCODE($test->results());
foreach ($test->results() as $result) {
    echo $result['text'].'<br>';
    if ($result['status'])
        echo 'Pass';
    else
        echo 'Fail ('.$result['source'].')';
    echo '<br><br>';
}
?>