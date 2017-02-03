<?php
/*
 * This file handles all logic relevant to carts
 * This does not include transitions from carts
 * to invoices. That is handled in the invoices.php file
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('include/common.php');

if(empty($_GET)) {
    //By default load all active carts
    $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
    $data['Carts'] = $db->get('Carts ca');
    $data['Message'] = "All Carts";
    $tpl = $m->loadTemplate('carts');
}
else if(!empty($_GET['action'])) {
    if ($_GET['action'] == 'all') {
        //Loads all carts
        $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
        $data['Carts'] = $db->get('Carts ca');
        $data['Username'] = "everyone";
        $data['Message'] = "All Carts";
        $tpl = $m->loadTemplate('carts');
    } else if ($_GET['action'] == 'edit') {
        //Opens the edit cart template
        $db->where('cartID', $_GET['cartID']);
        $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
        $data['Carts'] = $db->get('Carts ca');
        $data['Message'] = "Edit This Cart";
        $tpl = $m->loadTemplate('carts_edit');
    } else if ($_GET['action'] == 'update') {
        //Handles the submit of the edit form
        $data['Username'] = $_SESSION['user']['username'];
        $insertData = Array(
            'cartStartDate' => $_POST['cartStartDate'],
            'cartEndDate' => $_POST['cartEndDate'],
            'cartComments' => $_POST['cartComments'],
        );
        $db->where('cartID', $_POST['cartID']);
        if ($db->update('Carts', $insertData))
            $data['Message'] = "Cart successfully changed.";
        else
            $data['Message'] = "Cart edit failed.";
        //Edit successful - load carts for this user
        $db->where('userID', $_SESSION['user']['id']);
        $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
        $data['Carts'] = $db->get('Carts ca');
        $tpl = $m->loadTemplate('carts');
    } else if ($_GET['action'] == 'delete') {
        //Delete from Carts
        $db->where('cartID', $_GET['cartID']);
        $db->delete('Carts');
        //Delete from Cart Items
        $db->where('cartID', $_GET['cartID']);
        $db->delete('CartItems');
        $data['Message'] = "Cart deleted successfully.";
        //Cart deleted successfully - load all carts
        $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
        $data['Carts'] = $db->get('Carts ca');
        $data['Message'] = "All Carts";
        $tpl = $m->loadTemplate('carts');
    }
}
else if (!empty($_GET['search'])) {
    //Search all carts
    $search = "%" . $_GET['search'] . "%";
    $db -> where('customerFirstName', $search, 'LIKE');
    $db -> orWhere('customerLastName', $search, 'LIKE');
    $db -> orWhere('customerPhone', $search, 'LIKE');
    $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
    $data['Carts'] = $db -> get('Carts ca');
    $data['Message'] = "Search Results";
    $tpl = $m->loadTemplate('carts');
}
else if(!empty($_GET['cartID'])) {
    //Load the cart details template, including pricing info
    $db->where('cartID', $_GET['cartID']);
    $db->join('Customers cu', "cu.customerID=ca.customerID", "INNER");
    $data['CartDetails'] = $db->get('Carts ca');

    //Get the package column from the database
    $packageTotal = 0;
    if($data['CartDetails'][0]['cartPackage'] > 0){
        $packageTotal = $data['CartDetails'][0]['cartPackage'];
    }

    //Grab cart items for this cart
    $db->where('cartID', $_GET['cartID']);
    $db->join('Inventory i', "i.pID=ci.productID", "INNER");
    $data['CartItems'] = $db->get('CartItems ci');

    //Get the customer
    $customerID = $data['CartDetails']['0']['customerID'];
    $db->where('customerID', $customerID);
    $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
    $data['CustomerType'] = $db->getOne('Customers c');
    $data['Message'] = "Cart Details";

    //Figure out customer type column to query in prices
    $col = $data['CustomerType']['customerTypeName'];
    $colLower = strtolower($col);
    
    //Figure out if 1 or more days
    $startDate = new DateTime($data['CartDetails'][0]['cartStartDate']);
    $endDate = new DateTime($data['CartDetails'][0]['cartEndDate']);
    $difference = $startDate->diff($endDate);
    if($difference->format('%R%a days') > 1) {
        $colLower = $colLower . "Plus";
    }
    $data['priceColumn'] = $colLower;

    //Add Prices to each item array in CartItems
    $i=0;
    $total=0;
    foreach ($data['CartItems'] as $item) {
        $db->where('priceCategory',$item['pCategory']);  //
        $price = $db->get('Prices', 1, $colLower);
        if($price) {
            $total += $price[0][$colLower];
            array_push($data['CartItems'][$i], $price[0][$colLower]);
            $i++;
        }
        else {
            array_push($data['CartItems'][$i], 0);
            $i++;
        }
    }
    $multiple = $difference->format('%R%a days');
    $data['CartTotal'] = $total * $multiple;

    if($packageTotal > 0) {
        $data['PackageTotal'] = $packageTotal * $multiple;
    }

    $tpl = $m->loadTemplate('carts_details');
}

echo $tpl->render($data);
