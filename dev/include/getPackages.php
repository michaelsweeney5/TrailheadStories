<?php
/*
 * This file handles AJAX requests to get packages
 * Called in two locations, carts and inventory.
 * Just lists all the packages with a button to handle further logic
 */
session_start();
if(empty($_SESSION['user'])) {
    header("Location: ../index.php");
    die("Redirecting to Log In");
}
else {
    require_once('common.php');
    if($_GET['action'] == "getPackages") {
        $results = $db->get('Packages', null, "packageID,packageName");
        echo json_encode($results);
    }
    else if($_GET['action'] == "getPackagePrice") {
        $db->where('packageID',$_GET['packageID']);
        $results = $db->get('Packages', 1, $_GET['priceColumn']);
        echo json_encode($results);
    }
}