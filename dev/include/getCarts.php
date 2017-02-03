<?php

	require_once('common.php');
	$data = array();

	$startDateDay = substr($_GET['startDate'], 3, 2);
	$startDateMonth = substr($_GET['startDate'], 0, 2);
	$startDateYear = substr($_GET['startDate'], 6, 4);

	$endDateDay = substr($_GET['returnDate'], 3, 2);
	$endDateMonth = substr($_GET['returnDate'], 0, 2);
	$endDateYear = substr($_GET['returnDate'], 6, 4);

	$startDate = $startDateYear . "-" . $startDateMonth . "-" . $startDateDay;
	$endDate = $endDateYear . "-" . $endDateMonth . "-" . $endDateDay;

	// SELECT DISTINCT
	$carts = "SELECT * FROM Carts
           	  JOIN Customers ON Customers.customerID = Carts.customerID 
              WHERE Carts.cartStartDate = '".$startDate.
              "' AND Carts.cartEndDate = '".$endDate."'";
	
	$data = $db->query($carts);

	echo json_encode($data);
?>