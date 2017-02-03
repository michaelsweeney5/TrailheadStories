<?php
/*
 * This handles AJAX requests from the create cart modal when searching for customers
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: ../index.php");
    die("Redirecting to Log In");
}
require_once('common.php');
if(!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $db->where('customerFirstName', $search, 'LIKE');
    $db->orWhere('customerLastName', $search, 'LIKE');
    $db->orWhere('customerPhone', $search, 'LIKE');
    $results = $db->get('Customers');
    echo json_encode($results);
} else {
    header("Location: ../reservations.php");
    die("Redirecting to reservations page.");
}