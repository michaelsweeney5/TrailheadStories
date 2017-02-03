

<?php

require_once('common.php');

if($_GET['action'] == "singleDatePicker" && !empty($_GET['category']) && !empty($_GET['date']))
{
	$dataArray = array();
	$category = $_GET['category'];
	$date = $_GET['date'];

	$categoryData = $db->orderBy('cDescription', 'asc')
					   ->get('Categories');

	// ALL SUB-TREE ITEMS
	function GetInventoryCategories($categoryData, $parent, &$categoriesResult)
    {
      foreach($categoryData as $arr)
      {
        if($arr['cParent'] == $parent)
        {
          $categoriesResult[] = $arr['cCategory'];
          GetInventoryCategories($categoryData, $arr['cCategory'], $categoriesResult);
        }
      }
    }

    GetInventoryCategories($categoryData, $category, $categoriesResult);

    // Handles Error 500 when querying empty $result
    if(count($categoriesResult) == 0)
    {
        $categoriesResult[] = $category;
    }
    
    $subCategoriesItems = "SELECT Inventory.pID, Inventory.pName, Inventory.pDescription, Inventory.pCategory
                      FROM Inventory INNER JOIN Categories
                      ON Inventory.pCategory = Categories.cCategory
                      AND Inventory.pStatus = 'v'
                      AND Categories.cCategory IN (" . implode(',', $categoriesResult) . ")
                      ORDER BY Inventory.pName ASC";

    $items = $db->query($subCategoriesItems);
    $count = count($items);

    // GETS ALL INVOICES THAT CONTAIN CATEGORY ITEMS.  RESULT EQUALS ACCUMULATION OF ITEMS IN THE INVOICES. (i.e. 2 INVOICES, 5 ITEMS, RETURNS 5)
    for($i = 0; $i < count($items); $i++) $itemIDArray[] = $items[$i]['pID'];

    $currentInvoiceDates = "SELECT InvoiceItems.productID, Invoices.invoiceStartDate, Invoices.invoiceEndDate
						 FROM Invoices JOIN InvoiceItems ON Invoices.invoiceID = InvoiceItems.invoiceID
						 WHERE InvoiceItems.productID IN (" . implode(',', $itemIDArray) . ")";
	$invoiceDates = $db->query($currentInvoiceDates);

	$numItems = array();
	// GET QTY OF ITEMS FOR PARTICULAR DATE
	 for($i = 0; $i < count($invoiceDates); $i++)
	 {
	 	$startDate = $invoiceDates[$i]['invoiceStartDate'];
	 	$endDate = $invoiceDates[$i]['invoiceEndDate'];
	 	//array_push($dataArray, $invoiceDates[$i]['productID']);
	 	//array_push($dataArray, $startDate);
	 	//array_push($dataArray, $endDate);
	 	$numItemsPerDate = "SELECT * FROM InvoiceItems JOIN Invoices ON InvoiceItems.invoiceID = Invoices.invoiceID 
		WHERE InvoiceItems.productID = ".$invoiceDates[$i]['productID']." AND '".$date."' BETWEEN '".$startDate."' AND '".$endDate."'";
		$numItems[] = $db->query($numItemsPerDate);
		if(count($numItems[$i]) > 0) $count2 += 1;
	}

	if($count2 == 0) $totalResult = $count;
	else $totalResult = $count - $count2;

	array_push($dataArray, $totalResult);
    array_push($dataArray, $items);
    array_push($dataArray, $numItems);

	echo json_encode($dataArray);

}
else if(!empty($_GET['action']) && $_GET['action'] == "findConflicts")
{
	$dataArray = array();
	$result = array();

	$item = $_GET['product'];
	$startDate = $_GET['startDate'];
	$returnDate = $_GET['returnDate'];

	$allReservedDates = "SELECT invoiceStartDate, invoiceEndDate 
						 FROM Invoices JOIN InvoiceItems ON Invoices.invoiceID = InvoiceItems.invoiceID
						 WHERE InvoiceItems.productID = ".$item;
	$dates = $db->query($allReservedDates);

	if(count($dates) > 0)
	{
		for($i = 0; $i < count($dates); $i++)
		{
			if($dates[$i]['invoiceStartDate'] > $startDate && $dates[$i]['invoiceEndDate'] < $returnDate)
			{
				array_push($dataArray, "Conflict");
				$i = count($dates);
			}
			else array_push($dataArray, "No Conflict");
		}
	}
	else array_push($dataArray, "No Conflict");

	for($i = 0; $i < count($dataArray); $i++)
	{
		if($dataArray[$i] == "Conflict") array_push($result, "Conflict");
	}

	if($result[0] == "Conflict") echo json_encode($result);
	else 
	{
		array_push($result, "No Conflict");
		echo json_encode($result);
	}
}
else if(!empty($_GET['product']))
{
	$dataArray = array();

	$unavailableDates = "SELECT Invoices.invoiceStartDate, Invoices.invoiceEndDate
						 FROM Invoices JOIN InvoiceItems ON Invoices.invoiceID = InvoiceItems.invoiceID
						 WHERE InvoiceItems.productID = " . $_GET['product'];
	$datesUnavailable = $db->query($unavailableDates);

	$year = date("Y");
	$month = date("n");
	$day = date("j");

	array_push($dataArray, $_GET['product']);
	array_push($dataArray, $year);
	array_push($dataArray, $month);
	array_push($dataArray, $day);
	array_push($dataArray, $datesUnavailable);

	echo json_encode($dataArray);

	// If an item is returned early, the calendar should reflect a difference in available dates
}
?>