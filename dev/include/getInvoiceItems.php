<?php
/*
 * This file handles AJAX requests to get items in an invoice
 * Called by the drop down list buttons on invoices pages
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('common.php');
if(!empty($_GET['invoiceID'])) {
    $db->where('invoiceID', $_GET['invoiceID']);
    $db->join('Inventory i', "i.pID=ii.productID", "INNER");
    $results = $db->get('InvoiceItems ii');
    echo json_encode($results);
} else {
    header("Location: ../reservations.php");
    die("Redirecting to reservations page.");
}