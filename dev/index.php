<?php
/**
 * Main load point, if the use is not logged in they must authenticate
 * If the user is already logged in with a session they are move over to dashboard.php
 */
session_start();

if(!empty($_SESSION['user']))
{
    header("Location: dashboard.php");
    die("Redirecting to Dashboard");
}

require_once('include/common.php');

if(!empty($_POST))
{
    $db->where ("username", $_POST['username']);
    $result = $db->getOne("users");

    $login_ok = false;

    if($result)
    {
        $check_password = hash('sha256', $_POST['password'] . $result['salt']);
        for($round = 0; $round < 65536; $round++)
        {
            $check_password = hash('sha256', $check_password . $result['salt']);
        }

        if($check_password === $result['password'])
        {
            $login_ok = true;
        }
    }

    if($login_ok)
    {
        unset($result['salt']);
        unset($result['password']);

        $_SESSION['user'] = $result;
        session_write_close();

        header("Location: dashboard.php");
        die("Redirecting to Dashboard");
    }
    else
    {
        $message[] =  "Username or Password Incorrect Try Again";
    }
}
$tpl = $m->loadTemplate('index');
if(isset($message)) {
    $data['message'] = $message;
}
echo $tpl->render($data);

