<?php
/*
 * This file handles AJAX requests to get items in a cart
 * Called by the drop down list buttons on carts pages
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('common.php');
if(!empty($_GET['cartID'])) {
    $db->where('cartID', $_GET['cartID']);
    $db->join('Inventory i', "i.pID=ci.productID", "INNER");
    $results = $db->get('CartItems ci');
    echo json_encode($results);
} else {
    header("Location: ../reservations.php");
    die("Redirecting to reservations page.");
}