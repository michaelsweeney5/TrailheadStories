<?php
/*
* This is just a last-minute page addition for Dutch to delete items from packages
* The drop down listings call a php file in the include directory
* as do the trash can icons that actually delete the items
*/
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('include/common.php');

if(empty($_GET)) {
    $data['Packages'] = $db->get('Packages');
    $data['Message'] = "All Packages";
    $tpl = $m->loadTemplate('packages');
}
else if(!empty($_GET['action'])) {

}
echo $tpl->render($data);
