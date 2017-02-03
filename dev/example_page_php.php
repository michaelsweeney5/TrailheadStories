<?php
// Start Session
session_start();
// No User Session Send To Login
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
// Common to all pages PHP such as setup Db, Mustache, Time Settings, $data array.
require_once('include/common.php');

// Example of an error message to have show up at top of the content.
$message = "<div class='alert alert-warning' width='200' role='alert'>Example Error Message</div>";

// Put the Message in the $data arrray sent to be rendered.
array_push($data, $message, "message");

// Using the .mustache related to what you want to display
$tpl = $m->loadTemplate('example_page_layout');
echo $tpl->render($data);