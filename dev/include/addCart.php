<?php
/*
 * This file handles the AJAX submit from the create cart form
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('common.php');

if(!empty($_SESSION['user']['id'])) {
    //This handles a create cart request from the inventory version of create cart form
    if(!empty($_GET['productID']) && !empty($_GET['customerID']) && !empty($_GET['cartStartDate']) && !empty($_GET['cartEndDate']) && !empty($_GET['cartComments'])) {
        $cartInsertData = Array (
            "userID" => $_SESSION['user']['id'],
            "customerID" => $_GET['customerID'],
            "cartStartDate" => $_GET['cartStartDate'],
            "cartEndDate" => $_GET['cartEndDate'],
            "cartComments" => $_GET['cartComments']
        );
        $db->insert ('Carts', $cartInsertData);
        $id = $db->getInsertId();
        if($id) {
            $cartItemInsertData = Array(
                "productID" => $_GET['productID'],
                "cartID" => $id
            );
            $db->insert ('CartItems', $cartItemInsertData);
        }
        $db->where("cartID", $id);
        $result = $db->getOne("Carts");
        echo json_encode($result);
    }
    //This handles a create cart request from the carts or invoices page
    else if(!empty($_GET['customerID']) && !empty($_GET['cartStartDate']) && !empty($_GET['cartEndDate']) && !empty($_GET['cartComments'])) {
        $insertData = Array (
            "userID" => $_SESSION['user']['id'],
            "customerID" => $_GET['customerID'],
            "cartStartDate" => $_GET['cartStartDate'],
            "cartEndDate" => $_GET['cartEndDate'],
            "cartComments" => $_GET['cartComments']
        );
        $id = $db->insert ('Carts', $insertData);
        if($id)
            echo json_encode($id,$_GET['cartStartDate'],$_GET['cartEndDate']);
    }
} else {
    header("Location: index.php");
    die("Redirecting to login page.");
}