<?php
/*
 * This file handles AJAX requests to delete items from a cart
 * Called by the trash cans that appear next to cart items
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('common.php');
if(!empty($_GET['cartID']) && !empty($_GET['productID'])) {
    $db->where("(cartID = ? AND productID = ?)", Array($_GET['cartID'], $_GET['productID']));
    if($db->delete('CartItems')){
        echo "success";
    }
    else {
        echo "failure";
    }
} else {
    header("Location: ../carts.php");
    die("Redirecting to carts page.");
}