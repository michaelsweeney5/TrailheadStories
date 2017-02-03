<?php
/*
 * This file handles AJAX requests to get items in an archived invoice
 * Called by the drop down list buttons on the old invoices page
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('common.php');
if(!empty($_GET['oldInvoiceID'])) {
    $db->where('oldInvoiceID', $_GET['oldInvoiceID']);
    $db->join('Inventory i', "i.pID=oi.oldProductID", "INNER");
    $results = $db->get('OldInvoiceItems oi');
    echo json_encode($results);
} else {
    header("Location: ../reservations.php");
    die("Redirecting to reservations page.");
}