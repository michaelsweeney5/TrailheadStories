<?php
/* The invoices page handles all logic involving invoices.
 * This includes transitions of carts to invoices
 * Conflict checking logic using start and end dates occurs in this file
 */
session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}
require_once('include/common.php');

if(empty($_GET)) {
    //By default we load invoices for today's date
    $todaysDate = date('Y/m/d');
    $db -> where('invoiceStartDate', $todaysDate);
    $db -> join('Customers c', "c.customerID=i.customerID","INNER");
    $data['Invoices'] = $db->get('Invoices i');
    $data['Message'] = "Today's Reservations";

    $tpl = $m->loadTemplate('invoices');
}
else if(!empty($_GET['action'])) {
    //The action section is used to control the logic of view changes
    if ($_GET['action'] == 'all') {
        //Load all invoices, including customer info
        $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
        $data['Invoices'] = $db->get('Invoices i');
        $data['Message'] = "All Reservations";
        $tpl = $m->loadTemplate('invoices');
    }
    else if ($_GET['action'] == 'edit') {
        //Load the edit template - for this we need data from the invoice we're editing
        $db->where('invoiceID', $_GET['invoiceID']);
        $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
        $data['Invoices'] = $db->get('Invoices i');
        $data['Message'] = "Edit This Reservation";
        $tpl = $m->loadTemplate('invoices_edit');
    }
    else if ($_GET['action'] == 'update') {
        //This action actually submits the edit information

        //Get all invoice items from this invoice
        $db->where('invoiceID', $_POST['invoiceID']);
        $results = $db->get('InvoiceItems', null, 'productID');
        $invoiceItems = Array();
        foreach ($results as $row) {
            array_push($invoiceItems, $row['productID']);
        }

        //Grab any invoiceIDs that have any of these items
        $db->where('ii.productID', $invoiceItems, 'IN');
        $db->join('Invoices i',"i.invoiceID=ii.invoiceID", "INNER");
        $results = $db->get('InvoiceItems ii', null, 'ii.invoiceID');
        $invoiceIDs = Array();
        foreach ($results as $row) {
            if($row['invoiceID'] != $_POST['invoiceID']) {
                array_push($invoiceIDs, $row['invoiceID']);
            }
        }

        //Conflict checking starts here
        $conflicts = false;
        $uniqueInvoiceIDs = array_unique($invoiceIDs);
        if(count($uniqueInvoiceIDs) > 0) {
            //Grab start and end dates to compare from each invoiceID
            $db->where('invoiceID', $uniqueInvoiceIDs, 'IN');

            $dates = Array('invoiceStartDate', 'invoiceEndDate');
            $results = $db->get('Invoices', null, $dates);

            //Check for date conflicts
            $startDate = new DateTime($_POST['invoiceStartDate']);
            $startDate = date_format($startDate, "Y-m-d");
            $endDate = new DateTime($_POST['invoiceEndDate']);
            $endDate = date_format($endDate, "Y-m-d");

            foreach ($results as $row) {
                $testStartDate = new DateTime($row['invoiceStartDate']);
                $testStartDate = date_format($testStartDate, "Y-m-d");
                $testEndDate = new DateTime($row['invoiceEndDate']);
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
        
        if($conflicts) {
            //Found an item in a cart with conflicting dates.
            $data['Message'] = "Can't make changes; the new dates would create a conflict with another reservation.";
        }
        else {
            //No conflicts found. Finish edits of reservation
            $insertData = Array(
                'invoiceStartDate' => $_POST['invoiceStartDate'],
                'invoiceEndDate' => $_POST['invoiceEndDate'],
                'invoiceComments' => $_POST['invoiceComments'],
            );
            $db->where('invoiceID', $_POST['invoiceID']);
            if ($db->update('Invoices', $insertData)) {
                $data['Message'] = "Reservation successfully changed.";
            } else {
                $data['Message'] = "Reservation edit failed.";
            }
        }

        //Proceed to loading reservations page
        $db->join('Customers c', "c.customerID=i.customerID", "INNER");
        $data['Invoices'] = $db->get('Invoices i');
        $tpl = $m->loadTemplate('invoices');
    }
    else if ($_GET['action'] == 'add') {
        //This is where we've submitted a cart, so adding it to the reservations requires conflict checks
        if(!empty($_GET['cartID'])) {
            //Get all invoice items from this invoice
            $db->where('cartID', $_GET['cartID']);
            $results = $db->get('CartItems', null, 'productID');
            $invoiceItems = Array();
            foreach ($results as $row) {
                array_push($invoiceItems, $row['productID']);
            }

            //Grab any invoiceIDs that have any of these items
            $invoiceIDs = Array();
            if(count($invoiceItems) > 0) {
                $db->where('ii.productID', $invoiceItems, 'IN');
                $db->join('Invoices i', "i.invoiceID=ii.invoiceID", "INNER");
                $results = $db->get('InvoiceItems ii', null, 'ii.invoiceID');
                foreach ($results as $row) {
                    array_push($invoiceIDs, $row['invoiceID']);
                }
            }

            //Conflict checking starts here
            $conflicts = false;
            $uniqueInvoiceIDs = array_unique($invoiceIDs);
            if(count($uniqueInvoiceIDs) > 0) {
                //Grab start and end dates to compare from each invoiceID
                $db->where('invoiceID', $uniqueInvoiceIDs, 'IN');

                $dates = Array('invoiceStartDate', 'invoiceEndDate');
                $results = $db->get('Invoices', null, $dates);

                //Check for date conflicts
                $startDate = new DateTime($_GET['cartStartDate']);
                $startDate = date_format($startDate, "Y-m-d");
                $endDate = new DateTime($_GET['cartEndDate']);
                $endDate = date_format($endDate, "Y-m-d");

                foreach ($results as $row) {
                    $testStartDate = new DateTime($row['invoiceStartDate']);
                    $testStartDate = date_format($testStartDate, "Y-m-d");
                    $testEndDate = new DateTime($row['invoiceEndDate']);
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

            if($conflicts) {
                //Found an item in a cart with conflicting dates.
                $data['Message'] = "Can't make changes; the new dates would create a conflict with another reservation.";
            }
            else {
                $db->where('cartID', $_GET['cartID']);
                //Move Cart to Invoices
                $db->rawQuery('INSERT Invoices (customerID, invoiceComments, invoiceStartDate, invoiceEndDate, invoicePackage) SELECT customerID, cartComments, cartStartDate, cartEndDate, cartPackage FROM Carts WHERE cartID=' . $_GET['cartID']);
                $id = $db->getInsertId();
                //Move CartItems to InvoiceItems
                $db->rawQuery('INSERT InvoiceItems (invoiceID, productID) SELECT ' . $id . ', productID FROM CartItems WHERE cartID=' . $_GET['cartID']);
                $data['Message'] = "Reservation successfully completed.";
                if ($id) {
                    //Delete from Carts and CartItems
                    $db->where('cartID', $_GET['cartID']);
                    $db->delete('Carts');
                    $db->where('cartID', $_GET['cartID']);
                    $db->delete('CartItems');
                }
            }
        }
        $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
        $data['Invoices'] = $db->get('Invoices i');
        $tpl = $m->loadTemplate('invoices');

    }
    else if ($_GET['action'] == 'delete') {
        //This action deletes an invoice - needs to delete invoice items as well
        if (!empty($_GET['invoiceID'])) {
            $db->where('invoiceID', $_GET['invoiceID']);
            //Move Invoice to OldInvoices
            $db->rawQuery('INSERT OldInvoices (oldCustomerID, oldInvoiceComments, oldInvoiceStartDate, oldInvoiceEndDate) SELECT customerID, invoiceComments, invoiceStartDate, invoiceEndDate FROM Invoices WHERE invoiceID=' . $_GET['invoiceID']);
            $id = $db->getInsertId();
            //Move InvoiceItems to OldInvoiceItems
            $db->rawQuery('INSERT OldInvoiceItems (oldInvoiceID, oldProductID) SELECT ' . $id . ', productID FROM InvoiceItems WHERE invoiceID=' . $_GET['invoiceID']);
            //Delete from Invoices and InvoiceItems
            if($id) {
                $data['Message'] = "Reservation successfully deleted.";
                $db->where('invoiceID', $_GET['invoiceID']);
                $db->delete('Invoices');
                $db->where('invoiceID', $_GET['invoiceID']);
                $db->delete('InvoiceItems');
            }
            else {
                $data['Message'] = "Reservation delete fialed.";
            }
            //Load all reservations on conclusion
            $db->where('1=1');
            $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
            $data['Invoices'] = $db->get('Invoices i');
            $tpl = $m->loadTemplate('invoices');
        }
    }
    else if ($_GET['action'] == 'checkOut') {
        //The check out logic just sets a flag on the invoice
        if (!empty($_GET['invoiceID'])) {
            //Change value in table to checked out
            $insertData = Array(
                'invoiceCheckedOut' => '1'
            );
            $db->where('invoiceID', $_GET['invoiceID']);
            if ($db->update('Invoices', $insertData)) {
                $data['Message'] = "Reservation successfully checked out.";
            } else {
                $data['Message'] = "Reservation checkout failed.";
            }
            //Load all reservations on conclusion
            $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
            $data['Invoices'] = $db->get('Invoices i');
            $tpl = $m->loadTemplate('invoices');
        } //Otherwise load the check out template
        else {
            $db->where('invoiceCheckedOut', '1');
            $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
            $data['Invoices'] = $db->get('Invoices i');
            $data['Message'] = "Checked Out Reservations";
            $tpl = $m->loadTemplate('invoices_checked_out');
        }
    }
    else if ($_GET['action'] == 'checkIn') {
        /*
         * Checking in an item archives the invoice into old invoices,
         * along with the invoice items, and deletes the invocie from the invoices table
         * along with the invoice items
         */
        if (!empty($_GET['invoiceID'])) {
            //Change value in table to checked in
            $insertData = Array(
                'invoiceCheckedOut' => '0'
            );
            $db->where('invoiceID', $_GET['invoiceID']);
            if ($db->update('Invoices', $insertData)) {
                //Move Invoice to OldInvoices
                $db->rawQuery('INSERT OldInvoices (oldCustomerID, oldInvoiceComments, oldInvoiceStartDate, oldInvoiceEndDate) SELECT customerID, invoiceComments, invoiceStartDate, invoiceEndDate FROM Invoices WHERE invoiceid=' . $_GET['invoiceID']);
                $id = $db->getInsertId();
                //Move InvoiceItems to OldInvoiceItems
                $db->rawQuery('INSERT OldInvoiceItems (oldInvoiceID, oldProductID) SELECT ' . $id . ', productID FROM InvoiceItems WHERE invoiceID=' . $_GET['invoiceID']);
                //Delete from Invoices and InvoiceItems
                if($id) {
                    $data['Message'] = "Reservation successfully checked in.";
                    $db->where('invoiceID', $_GET['invoiceID']);
                    $db->delete('Invoices');
                    $db->where('invoiceID', $_GET['invoiceID']);
                    $db->delete('InvoiceItems');
                }
            } else {
                $data['Message'] = "Reservation check in failed.";
            }
            //Load all reservations on conclusion
            $db->where('1=1');
            $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
            $data['Invoices'] = $db->get('Invoices i');
            $tpl = $m->loadTemplate('invoices');
        }
    }
    else if ($_GET['action'] == 'old') {
        //View the archived invoices. This is for report running, which is not implemented
        $db->join('Customers cu', "cu.customerID=i.oldCustomerID", "INNER");
        $data['OldInvoices'] = $db->get('OldInvoices i');
        $data['Message'] = "Old Reservations";
        $tpl = $m->loadTemplate('invoices_old');
    }
    else if ($_GET['action'] == 'restore') {
        /*
         * This will move an old invoice to the current carts table.
         * Maybe useful for building reservations for a regular customer
        */
        if(!empty($_GET['oldInvoiceID'])) {
            $db->where('invoiceID', $_GET['oldInvoiceID']);
            //Move Invoice to OldInvoices
            $db->rawQuery('INSERT Carts (customerID, cartComments, cartStartDate, cartEndDate) SELECT oldCustomerID, oldInvoiceComments, oldInvoiceStartDate, oldInvoiceEndDate FROM OldInvoices WHERE oldInvoiceID=' . $_GET['oldInvoiceID']);
            $id = $db->getInsertId();
            //Move InvoiceItems to OldInvoiceItems
            $db->rawQuery('INSERT CartItems (cartID, productID) SELECT ' . $id . ', oldProductID FROM OldInvoiceItems WHERE oldInvoiceID=' . $_GET['oldInvoiceID']);
            //Delete from OldInvoices and OldInvoiceItems
            if($id) {
                $data['Message'] = "Cart successfully restored.";
                $db->where('oldInvoiceID', $_GET['oldInvoiceID']);
                $db->delete('OldInvoices');
                $db->where('oldInvoiceID', $_GET['oldInvoiceID']);
                $db->delete('OldInvoiceItems');
            }
            else {
                $data['Message'] = "Cart restoration failed.";
            }
            //Redirect to carts
            $db->where('1=1');
            $db->join('Customers cu', "cu.customerID=i.customerID", "INNER");
            $data['Carts'] = $db->get('Carts i');
            $tpl = $m->loadTemplate('carts');
            header("Location: carts.php");
            die("Redirecting to carts");
        }
    }
}
else if (!empty($_GET['search'])) {
    //Searches current invoices
    $search = "%" . $_GET['search'] . "%";
    $db -> where('customerFirstName', $search, 'LIKE');
    $db -> orWhere('customerLastName', $search, 'LIKE');
    $db -> orWhere('customerPhone', $search, 'LIKE');
    $db -> join('Customers c', "c.customerID=i.customerID","INNER");
    $data['Invoices'] = $db -> get('Invoices i');
    $data['Message'] = "Search Results";
    $tpl = $m->loadTemplate('invoices');
}
else if(!empty($_GET['searchOld'])) {
    //Searches the archived invoices
    $search = "%" . $_GET['searchOld'] . "%";
    $db -> where('customerFirstName', $search, 'LIKE');
    $db -> orWhere('customerLastName', $search, 'LIKE');
    $db -> orWhere('customerPhone', $search, 'LIKE');
    $db -> join('Customers c', "c.customerID=i.oldCustomerID","INNER");
    $data['OldInvoices'] = $db -> get('OldInvoices i');
    $data['Message'] = "Search Results";
    $tpl = $m->loadTemplate('invoices_old');
}
else if (!empty($_GET['invoiceID'])) {
    //This loads the invoice detail page, along with the pricing suggestions
    $db -> where('invoiceID', $_GET['invoiceID']);
    $db -> join('Customers c', "c.customerID=i.customerID","INNER");
    $data['InvoiceDetails'] = $db -> get('Invoices i');

    $packageTotal = 0;
    if($data['InvoiceDetails'][0]['invoicePackage'] > 0){
        $packageTotal = $data['InvoiceDetails'][0]['invoicePackage'];
    }

    $db->where('invoiceID', $_GET['invoiceID']);
    $db->join('Inventory i', "i.pID=ii.productID", "INNER");
    $data['InvoiceItems'] = $db->get('InvoiceItems ii');

    $customerID = $data['InvoiceDetails']['0']['customerID'];
    $db->where('customerID', $customerID);
    $db->join('CustomerType ct', 'c.customerType=ct.customerTypeID');
    $data['CustomerType'] = $db->getOne('Customers c');

    $data['Message'] = "Reservation Details";

    //Figure out customer type column to query in prices
    $col = $data['CustomerType']['customerTypeName'];
    $colLower = strtolower($col);

    //Figure out if 1 or more days
    $startDate = new DateTime($data['InvoiceDetails'][0]['invoiceStartDate']);
    $endDate = new DateTime($data['InvoiceDetails'][0]['invoiceEndDate']);
    $difference = $startDate->diff($endDate);
    if($difference->format('%R%a days') > 1) {
        $colLower = $colLower . "Plus";
    }
    $data['priceColumn'] = $colLower;

    //Add Prices to each item array in InvoiceItems
    $i=0;
    $total=0;
    foreach ($data['InvoiceItems'] as $item) {
        $db->where('priceCategory', $item['pCategory']);
        $price = $db->get('Prices', 1, $colLower);
        if($price) {
            $total += $price[0][$colLower];
            array_push($data['InvoiceItems'][$i], $price[0][$colLower]);
            $i++;
        }
        else {
            array_push($data['InvoiceItems'][$i], 0);
            $i++;
        }
    }
    $multiple = $difference->format('%R%a days');
    $data['InvoiceTotal'] = $total * $multiple;

    if($packageTotal > 0) {
        $data['PackageTotal'] = $packageTotal * $multiple;
    }

    $tpl = $m->loadTemplate('invoices_details');
}
else if (!empty($_GET['customerID'])) {
    //This loads invoices for a specific customer
    $db -> where('i.customerID', $_GET['customerID']);
    $db -> join('Customers c', "c.customerID=i.customerID","INNER");
    $data['Invoices'] = $db -> get('Invoices i');
    $data['Message'] = "Reservations For This Customer";
    $tpl = $m->loadTemplate('invoices');
}

echo $tpl->render($data);
