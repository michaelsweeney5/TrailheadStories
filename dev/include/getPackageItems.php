<?php
/*
 * This file handles AJAX requests to get items in a package
 * This also handles conflicts by only returning items that aren't
 * in an invoice within the date range provided
 */
session_start();
if(empty($_SESSION['user'])) {
    header("Location: ../index.php");
    die("Redirecting to Log In");
}
else {
    require_once('common.php');
    if($_GET['action'] == 'getItems') {
        //Get a start and end date from the cart
        $db->where("cartID", $_GET['cartID']);
        $results = $db->get('Carts', null, "cartStartDate, cartEndDate");
        $startDate = new DateTime($results[0]["cartStartDate"]);
        $startDate = date_format($startDate, "Y-m-d");
        $endDate = new DateTime($results[0]["cartEndDate"]);
        $endDate = date_format($endDate, "Y-m-d");
        
        //Get current cart items
        $db->where('cartID', $_GET['cartID']);
        $results = $db->get('CartItems', null, 'productID');
        $cartItems = Array();
        foreach ($results as $row) {
            array_push($cartItems, $row['productID']);
        }

        //Get all inventory items for that package
        $db->join('Inventory i', "i.pCategory=pi.categoryID", "INNER");
        $db->where("pi.packageID", $_GET['packageID']);
        $results = $db->get('PackageItems pi', null, "pID,pDescription");

        $availableProducts = Array();
        $i = 0;
        foreach ($results as $row) {
            $productID = $row['pID'];
            $db->join('Invoices i', "i.invoiceID=ii.invoiceID", "INNER");
            $db->where("productID", $productID);
            $conflicts = false;
            $dates = $db->get('InvoiceItems ii', null, "i.invoiceStartDate, i.invoiceEndDate");
            if ($dates) {
                //Check for date conflicts
                foreach ($dates as $dateRow) {
                    $testStartDate = new DateTime($dateRow['invoiceStartDate']);
                    $testStartDate = date_format($testStartDate, "Y-m-d");
                    $testEndDate = new DateTime($dateRow['invoiceEndDate']);
                    $testEndDate = date_format($testEndDate, "Y-m-d");
                    if ($testStartDate <= $startDate && $startDate <= $testEndDate) {
                        $conflicts = true;
                    }
                    else if ($testStartDate <= $endDate && $endDate <= $testEndDate) {
                        $conflicts = true;
                    }
                    else if ($startDate <= $testStartDate && $testStartDate <= $endDate) {
                        $conflicts = true;
                    }
                    else if ($startDate <= $testEndDate && $testEndDate <= $endDate) {
                        $conflicts = true;
                    }
                    else if ($startDate <= $testStartDate && $testEndDate <= $endDate) {
                        $conflicts = true;
                    }
                    else if ($testStartDate <= $startDate && $endDate <= $testEndDate) {
                        $conflicts = true;
                    }
                    else if ($testStartDate == $startDate || $testEndDate == $startDate) {
                        $conflicts = true;
                    }
                    else if ($testStartDate == $endDate || $testEndDate == $endDate) {
                        $conflicts = true;
                    }
                }
            } 
            if(!$conflicts) {
                if(!(in_array($row['pID'], $cartItems))) {
                    $availableProducts[$i]['pID'] = $row['pID'];
                    $availableProducts[$i]['pDescription'] = $row['pDescription'];
                    $i++;
                }
            }
        }
        
        //Finally, send data back
        if (count($availableProducts) > 0) {
            echo json_encode($availableProducts);
        } else {
            $noItemsAvailable = Array();
            $noItemsAvailable[0]['pID'] = 0;
            $noItemsAvailable[0]['pDescription'] = "No items available";
            echo json_encode($noItemsAvailable);
        }
    }
    else if($_GET['action'] == 'addToCart') {
        //This adds package items to a cart
        if(!empty($_GET['cartID']) && !empty($_GET['productID'])) {
            $insertData = Array(
                'cartID' => $_GET['cartID'],
                'productID' => $_GET['productID']
            );
            if ($db->insert('CartItems', $insertData)) {
                echo "Success";
            }
            else {
                echo "Failure";
            }
        }
    }
    else if($_GET['action'] == "setTotal") {
        //This handles the package deal total price
        if(!empty($_GET['cartID']) && !empty($_GET['multiple']) && !empty($_GET['packageID']) && !empty($_GET['customerID'])) {
            //Grab customer type and convert
            $db->where('customerID', $_GET['customerID']);
            $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
            $col = $db->get('Customers c', null, 'ct.customerTypeName');
            $colLower = strtolower($col[0]['customerTypeName']);

            //Figure out if 1 or more days
            $db->where('cartID', $_GET['cartID']);
            $results = $db->get('Carts', null, 'cartStartDate, cartEndDate');
            $startDate = new DateTime($results[0]['cartStartDate']);
            $endDate = new DateTime($results[0]['cartEndDate']);
            $difference = $startDate->diff($endDate);
            if($difference->format('%R%a days') > 1) {
                $colLower = $colLower . "Plus";
            }
            
            //Get price of package based on customer type above
            $db->where('packageID', $_GET['packageID']);
            $packagePrice = $db->get('Packages', null, $colLower);
            $total = (int)$_GET['multiple'] * $packagePrice[0][$colLower];
            $insertData = Array(
                'cartPackage' => $total
            );
            $db->where('cartID', $_GET['cartID']);
            if ($db->update('Carts', $insertData)) {
                echo "Success";
            }
            else {
                echo "Failure";
            }
        }
    }
    else if($_GET['action'] == "getOnePackageItems") {
        //This gets the package items for a specific package on the edit package page
        if(!empty($_GET['packageID'])) {
            $db->join('Inventory i', "i.pCategory=pi.categoryID", "INNER");
            $db->where('packageID', $_GET['packageID']);
            $results = $db->get('PackageItems pi');
            echo json_encode($results);
        }
        else {
            header("Location: ../index.php");
            die("Redirecting to Log In");
        }
    }
    else if($_GET['action'] == "deletePackageItem") {
        //Deletes an item from a package
       if(!empty($_GET['packageID']) && !empty($_GET['categoryID'])) {
           $db->where('packageID', $_GET['packageID']);
           $db->where('categoryID', $_GET['categoryID']);
           if($db->delete('PackageItems')) {
               echo json_encode("Success");
           }
           else {
               echo json_encode("Failure");
           }
           
       }
    }
    else {
        header("Location: ../index.php");
        die("Redirecting to Log In");
    }
}