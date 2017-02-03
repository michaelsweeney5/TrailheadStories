<?php
/*
 * This file handles all logic relevant to customers
 */
session_start();
if (empty($_SESSION['user'])) {
    header("Location: index.php");
    die("Redirecting to Log In");
}

require_once('include/common.php');

if (!empty($_GET['cID'])) {
    //Load the customer details template
    $db->where('customerID', $_GET['cID']);
    $data['customers'] = $db->getOne('Customers');
    $db->where('customerTypeID', $data['customers']['customerType']);
    $data['customers']['customerType'] = $db->getOne('CustomerType');
    $tpl = $m->loadTemplate('customers_info');
} else if (!empty($_GET['search'])) {
    //Search all customers
    $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
    $search = "%" . $_GET['search'] . "%";
    $db->where('customerFirstName', $search, 'LIKE');
    $db->orWhere('customerLastName', $search, 'LIKE');
    $data['customers'] = $db->get('Customers c');
    $tpl = $m->loadTemplate('customers_list');
} else if (!empty($_GET['action'])) {
    if ($_GET['action'] == 'add') {
        //Load the add new customer form
        $tpl = $m->loadTemplate('customers_add');
    }
    else if ($_GET['action'] == 'insert') {
        //Insert customer
        echo $_POST['customerWaiver'];
        if (!empty($_POST['customerType']) && !empty($_POST['customerFirstName']) && !empty($_POST['customerLastName']) && !empty($_POST['customerEmail']) && !empty($_POST['customerStreet']) && !empty($_POST['customerCity']) && !empty($_POST['customerState']) && !empty($_POST['customerZipCode']) && !empty($_POST['customerPhone']) && !empty($_POST['customerDriverLicense'])) {
            $insertData = Array(
                "customerType" => $_POST['customerType'],
                "customerFirstName" => $_POST['customerFirstName'],
                "customerLastName" => $_POST['customerLastName'],
                "customerEmail" => $_POST['customerEmail'],
                "customerStreet" => $_POST['customerStreet'],
                "customerCity" => $_POST['customerCity'],
                "customerState" => $_POST['customerState'],
                "customerZipCode" => $_POST['customerZipCode'],
                "customerPhone" => $_POST['customerPhone'],
                "customerDriverLicense" => $_POST['customerDriverLicense'],
                "customerComment" => $_POST['customerComment'],
                "customerWaiver" => $_POST['customerWaiver']
            );
            if ($db->insert('Customers', $insertData)) {
                $data['Message'] = "Customer added successfully.";
            } else {
                $data['Message'] = "Customer add failed.";
            }
            $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
            $data['customers'] = $db->get('Customers c');
            $tpl = $m->loadTemplate('customers_list');
        } else {
            $data['Message'] = "Please fill out all fields.";
            $data['post'] = $_POST;
            $tpl = $m->loadTemplate('customers_list');
        }
    }
    else if ($_GET['action'] == 'edit') {
        //Load edit customers page
        if (!empty($_GET['customerID'])) {
            $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
            $db->where('customerID', $_GET['customerID']);
            $data['customers'] = $db->getOne('Customers c');
            $tpl = $m->loadTemplate('customers_edit');
        }
    }
    else if ($_GET['action'] == 'update') {
        //Handles the updates from the edit customer form
        $insertData = Array(
            "customerType" => $_POST['customerType'],
            "customerFirstName" => $_POST['customerFirstName'],
            "customerLastName" => $_POST['customerLastName'],
            "customerEmail" => $_POST['customerEmail'],
            "customerStreet" => $_POST['customerStreet'],
            "customerCity" => $_POST['customerCity'],
            "customerState" => $_POST['customerState'],
            "customerZipCode" => $_POST['customerZipCode'],
            "customerPhone" => $_POST['customerPhone'],
            "customerDriverLicense" => $_POST['customerDriverLicense'],
            "customerComment" => $_POST['customerComment'],
            "customerWaiver" => $_POST['customerWaiver']
        );
        $db->where('customerID', $_POST['customerID']);
        if ($db->update('Customers', $insertData)) {
            $data['Message'] = "Customer successfully changed.";
        }
        else {
            $data['Message'] = "Customer edit failed.";
        }
        //Edit successful - load customers list
        $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
        $search = "%" . $_GET['search'] . "%";
        $db->where('customerFirstName', $search, 'LIKE');
        $db->orWhere('customerLastName', $search, 'LIKE');
        $data['customers'] = $db->get('Customers c');
        $tpl = $m->loadTemplate('customers_list');
    }
    else if ($_GET['action'] == 'delete') {
        //Delete a customer
        $db->where('customerID', $_GET['customerID']);
        if ($db->delete('Customers')) {
            $data['Message'] = "Customer deleted successfully";
        }
        else {
            $data['Message'] = "Customer delete failed";
        }
        $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
        $data['customers'] = $db->get('Customers c');
        $tpl = $m->loadTemplate('customers_list');
    }
    else if ($_GET['action'] == 'signWaiver')  {
        //Just update the waiver column
        $db->where('customerID', $_GET['customerID']);
        $insertData = Array(
            "customerWaiver" => $_POST['customerWaiver']
        );
        if ($db->update('Customers', $insertData)) {
            $data['Message'] = "Waiver successfully updated.";
        }
        else {
            $data['Message'] = "Waiver update failed.";
        }
        //Edit successful - load customers list
        $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
        $search = "%" . $_GET['search'] . "%";
        $db->where('customerFirstName', $search, 'LIKE');
        $db->orWhere('customerLastName', $search, 'LIKE');
        $data['customers'] = $db->get('Customers c');
        $tpl = $m->loadTemplate('customers_list');
    }
    else if ($_GET['action'] == 'editBalance') {
        //Just update the customer's balance
        if(!empty($_GET['customerID'])) {
            //Edit balance
            if(empty($_GET['newBalance'])) {
                $newBalance = 0;
            }
            else {
                $newBalance = $_GET['newBalance'];
            }
            $db->where('customerID', $_GET['customerID']);
            $insertData = Array(
                'customerBalance' => $newBalance
            );
            $db->update('Customers', $insertData);
            
            //Now load page again
            $db->where('customerID', $_GET['customerID']);
            $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
            $data['customers'] = $db->get('Customers c');
            $tpl = $m->loadTemplate('customers_info');
        }
    }
}
else {
    $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
    $data['customers'] = $db->get('Customers c');
    $tpl = $m->loadTemplate('customers_list');
}

echo $tpl->render($data);