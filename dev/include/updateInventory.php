<?php
session_start();
if(empty($_SESSION['user']))
{
    header("Location: inventory.php");
    die("Redirecting to Inventory");
}

require_once('common.php');

// ADD SUB-CATEGORIES
if($data['admin'] && $_GET['action'] == "verifyCategory" && !empty($_GET['description']) && !empty($_GET['category']))
{
	// Verify User Input From Modal
	$dataArray = array();
	$parent = $_GET['category'];
	$description = $_GET['description'];

	$descriptionVerification = "SELECT * FROM Categories WHERE cCategory = ".$parent." AND cDescription = '".$description.
	"' OR cParent = ".$parent." AND cDescription = '".$description."'";

	$results = $db->query($descriptionVerification);

	if(count($results) == 0)
	{
		array_push($dataArray, "Description Valid");
		array_push($dataArray, $description);
		array_push($dataArray, $parent);
		echo json_encode($dataArray);
	}
	else if(count($results) > 0)
	{
		array_push($dataArray, "Description Invalid");
		echo json_encode($dataArray);
	}
}
else if($data['admin'] && $_GET['action'] == "checkCurrentCategory" && !empty($_GET['category']))
{
	// Make New Category For Pre-Existing Inventory Item
	$existsArray = array();
	$category = $_GET['category'];

	$preExisting = "SELECT * FROM Inventory WHERE pCategory=".$category;
	$exists = $db->query($preExisting);

	if(count($exists) > 0)
	{
		array_push($existsArray, "true");
		array_push($existsArray, $exists);
		echo json_encode($existsArray);
	}
	else
	{
		array_push($existsArray, "false");
		echo json_encode($existsArray);
	}
}
else if($data['admin'] && $_GET['action'] == "moveCategory" && !empty($_GET['description']) && !empty($_GET['parent']))
{
	$movedCategoryArray = array();
	$description = $_GET['description'];
 	$parent = $_GET['parent'];

  	$insert = "INSERT INTO Categories VALUES (NULL,".$parent.",'".$description."')";
  	$db->query($insert);

 	$discover = "SELECT MAX(cCategory) AS newCategory FROM Categories";
 	$newCategory = $db->query($discover);

 	$updateInventory = "UPDATE Inventory SET pCategory = ".$newCategory[0]['newCategory']." WHERE pCategory = ".$parent;
 	$db->query($updateInventory);

 	$checkInventoryUpdate = "SELECT * FROM Inventory WHERE pCategory = ".$newCategory[0]['newCategory'];
 	$verification = $db->query($checkInventoryUpdate);

  	if(count($verification) > 0)
  	{
  		array_push($movedCategoryArray, "true");
  		array_push($movedCategoryArray, $verification);
  		echo json_encode($movedCategoryArray);
  	}
  	else
  	{
  	 	array_push($movedCategoryArray, "false");
  	 	array_push($movedCategoryArray, $description);
  	 	echo json_encode($movedCategoryArray);
  	}
}
else if($data['admin'] && $_GET['action'] == "insertCategory" && !empty($_GET['description']) && !empty($_GET['parent']))
{
	// Make New User-Defined Category
	$newArray = array();
	$description = $_GET['description'];
 	$parent = $_GET['parent'];

  	$insert = "INSERT INTO Categories VALUES (NULL,".$parent.",'".$description."')";
  	$db->query($insert);

 	$discover = "SELECT MAX(cCategory) FROM Categories";
 	$success = $db->query($discover);

 	if(count($success) != 0)
 	{
  		array_push($newArray, $success);
 		echo json_encode($newArray);
 	}
}


// EDIT CATEGORY (CHECK EDGE CASES)
else if($data['admin'] && $_GET['action'] == "editCategory" && !empty($_GET['name']) && !empty($_GET['category']) && !empty($_GET['parent']))
{
	$dataArr = array();

	$name = $_GET['name'];
	$category = $_GET['category'];
	$parent = $_GET['parent'];

	$verify = "SELECT * FROM Categories WHERE cDescription = '".$name."' AND cCategory = ".$parent.
	 		  " OR cDescription = '".$name."' AND cCategory = ".$category.
	 		  " OR cDescription = '".$name."' AND cParent = ".$category;
	$nameExists = $db->query($verify);

	if(count($nameExists) == 0)
	{
	 	$edit = "UPDATE Categories SET cDescription = '".$name."' WHERE cCategory = ".$category;
	 	$db->query($edit);

	 	$verify = "SELECT * FROM Categories WHERE cDescription = '".$name."' AND cCategory = ".$category;
	 	$result = $db->query($verify);

	 	if($result > 0)
	 	{ 
	 		array_push($dataArr, "Edit Successful");
	 		array_push($dataArr, $result);
	 		echo json_encode($dataArr);
	 	}
	}
	else
	{
		array_push($dataArr, "Edit Failed");
	 	array_push($dataArr, $result);
	 	echo json_encode($dataArr);
	}
}

// DELETE CATEGORY
else if($data['admin'] && $_GET['action'] == "deleteCategory" && !empty($_GET['name']) && !empty($_GET['category']) && !empty($_GET['parent']))
{
	$dataArr = array();

	$name = $_GET['name'];
	$category = $_GET['category'];
	$parent = $_GET['parent'];
	//echo json_encode("Deleting...");

	$delete = "DELETE FROM Categories WHERE cCategory = ".$category;
	$db->query($delete);

	$verify = "SELECT * FROM Categories WHERE cCategory = ".$category;
	$result = $db->query($verify);

	if(count($result) == 0)
	{
	 	array_push($dataArr, "Delete Successful");
	 	array_push($dataArr, $name);
	 	echo json_encode($dataArr);
	}
	else
	{
	 	array_push($dataArr, "Delete Failed");
	 	array_push($dataArr, $name);
		echo json_encode($dataArr);
	}
}

// EDIT INVENTORY PRICE
else if($data['admin'] && $_GET['action'] == "editPrice" && !empty($_GET['category']) 
	&& !empty($_GET['student1']) && !empty($_GET['student2']) && !empty($_GET['fcltyStfAlum1']) && !empty($_GET['fcltyStfAlum2']) 
	&& !empty($_GET['public1']) && !empty($_GET['public2']))
{
	$dataArray = array();
	$category = $_GET['category'];
	$student1 = $_GET['student1'];
	$student2 = $_GET['student2'];
	$facultyStaffAlumni1 = $_GET['fcltyStfAlum1'];
	$facultyStaffAlumni2 = $_GET['fcltyStfAlum2'];
	$public1 = $_GET['public1'];
	$public2 = $_GET['public2'];

	$checkForExisting = "SELECT * FROM Prices WHERE priceCategory = ".$category;
	$exists = $db->query($checkForExisting);

	// FOR COMPARING NEW TO OLD
	$exID = $exists[0]['priceID'];
	$exCategory = $exists[0]['priceCategory'];
	$exStd1 = $exists[0]['student'];
	$exStd2 = $exists[0]['studentPlus'];
	$exFaculty1 = $exists[0]['facultyStaffAlumn'];
	$exFaculty2 = $exists[0]['facultyStaffAlumnPlus'];
	$exPublic1 = $exists[0]['public'];
	$exPublic2 = $exists[0]['publicPlus'];

	if(count($exists) == 0)
	{
		$insert = "INSERT INTO Prices VALUES(NULL, ".$category.",".$student1.",".$student2.",".$facultyStaffAlumni1.",".
			$facultyStaffAlumni2.",".$public1.",".$public2.")";
		$db->query($insert);

		$check = "SELECT * FROM Prices WHERE priceCategory=".$category;
		$result = $db->query($check);

		if(count($result) > 0)
		{
			array_push($dataArray, "Add Price Successful");
			echo json_encode($dataArray);
		}
		else 
		{
			array_push($dataArray, "Edit Price Failed");
			array_push($dataArray, "Unexpected Error");
			echo json_encode($dataArray);
		}
	}
	else if(count($exists) > 0)
	{
		$update = "UPDATE Prices SET student=".$student1.", studentPlus=".$student2.", facultyStaffAlumn=".$facultyStaffAlumni1.
		", facultyStaffAlumnPlus=".$facultyStaffAlumni2.", public=".$public1.", publicPlus=".$public2." WHERE priceCategory=".$category;
		$db->query($update);

		$check = "SELECT * FROM Prices WHERE priceCategory=".$category;
		$result = $db->query($check);

		if($result[0]['priceID'] == $exID && $result[0]['priceCategory'] == $exCategory && $result[0]['student'] == $exStd1 
			&& $result[0]['studentPlus'] == $exStd2 && $result[0]['facultyStaffAlumn'] == $exFaculty1
			&& $result[0]['facultyStaffAlumnPlus'] == $exFaculty2 && $result[0]['public'] == $exPublic1 && $result[0]['publicPlus'] == $exPublic2)
		{
			array_push($dataArray, "Edit Price Failed");
			array_push($dataArray, "Values Already Exist");
			echo json_encode($dataArray);
		}
		else
		{
			array_push($dataArray, "Update Price Successful");
			array_push($dataArray, $result);
			echo json_encode($dataArray);
		}
	}
}

// DELETE INVENTORY.  Send item to Inventory Archive.  With Add Inventory Button on Category view, query archive first.
else if($data['admin'] && $_GET['action'] == "deleteFromInventory" && !empty($_GET['name']) && !empty($_GET['description']) && !empty($_GET['category']))
{
	$dataArray = array();
	$name = $_GET['name'];
	$description = $_GET['description'];
	$category = $_GET['category'];

	$getParent = "SELECT cParent FROM Categories WHERE cCategory = ".$category;
	$parent = $db->query($getParent);

	// Move to InventoryArchive
	$allItems = "SELECT * FROM Inventory WHERE pCategory = " . $category;
	$dataArray = $db->query($allItems);

	for($i = 0; $i < count($dataArray); $i++)
	{
	 	$archive = "INSERT INTO InventoryArchive VALUES (".$dataArray[$i]['pID'].",".$dataArray[$i]['pCategory'].",'".
	 		$dataArray[$i]['pName']."','".$dataArray[$i]['pDescription']."',".$dataArray[$i]['pLifespan'].",'".$dataArray[$i]['pPurchaseDate']."','".
	 		$dataArray[$i]['pCost']."',".$dataArray[$i]['pReservable'].",'".$dataArray[$i]['pStatus']."')";
			
			$db->query($archive);
	}

	$verifyArchived = "SELECT * FROM InventoryArchive WHERE pCategory = ".$category;
	$archivedResult = $db->query($verifyArchived);

	$deleteInventoryItem = "DELETE FROM Inventory WHERE pCategory = ".$category;
	$db->query($deleteInventoryItem);

	$verifyInventoryDelete = "SELECT * FROM Inventory WHERE pCategory = ".$category;
	$deletedInventoryResult = $db->query($verifyInventoryDelete);

	$deleteCategoryButton = "DELETE FROM Categories WHERE cCategory = ".$category;
	$db->query($deleteCategoryButton);

	$verifyCategoryDelete = "SELECT * FROM Categories WHERE cCategory = ".$category;
	$deletedCategoryResult = $db->query($verifyCategoryDelete);

	if(count($archivedResult) > 0 && count($deletedInventoryResult) == 0 && count($deletedCategoryResult) == 0)
	{
		array_push($dataArray, "Archived Successful");
		array_push($dataArray, $parent);
		array_push($dataArray, $name);
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}
	else
	{
		array_push($dataArray, "Archived Failed");
		array_push($dataArray, $name);
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}
}

// INCREMENT INVENTORY ITEM
else if($data['admin'] && $_GET['action'] == "incrementNew" && !empty($_GET['category']) && !empty($_GET['date']) && !empty($_GET['cost']))
{
	// CHECK FOR INACTIVE ITEMS FIRST
	$dataArray = array();
	$category = $_GET['category'];
	$date = $_GET['date'];
	$cost = $_GET['cost'];

	$count1 = "SELECT COUNT(pID) AS count1 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result1 = $db->query($count1);

	$inventoryInfo = "SELECT * FROM Inventory WHERE pCategory = ".$category;
	$inventory = $db->query($inventoryInfo);

	$insert = "INSERT INTO Inventory VALUES (NULL,".$inventory[0]['pCategory'].",'".
	 		$inventory[0]['pName']."','".$inventory[0]['pDescription']."',".$inventory[0]['pLifespan'].",'".$date."','".
	 		$cost."',".$inventory[0]['pReservable'].",'v')";
	$db->query($insert);

	$count2 = "SELECT COUNT(pID) AS count2 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result2 = $db->query($count2);

	$getDescription = "SELECT pName, pDescription FROM Inventory WHERE pCategory = ".$category;
	$description = $db->query($getDescription);

	if($result2[0]['count2'] == $result1[0]['count1']+1)
	{
		array_push($dataArray, "Incrementation Successful");
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}	
	else 
	{
		array_push($dataArray, "Incrementation Failed");
		array_push($dataArray, $description);
		echo json_encode($dataArray);	
	}
}

else if($data['admin'] && $_GET['action'] == "incrementFromRepair" && !empty($_GET['category']))
{
	// CHECK FOR INACTIVE ITEMS FIRST
	$dataArray = array();
	$category = $_GET['category'];

	$count1 = "SELECT COUNT(pID) AS count1 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result1 = $db->query($count1);

	$getMax = "SELECT MAX(pID) AS maxID FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'r'";
	$max = $db->query($getMax);

	$flagAsViewable = "UPDATE Inventory SET pStatus = 'v' WHERE pID = ".$max[0]['maxID'];
	$db->query($flagAsViewable);
	

	$count2 = "SELECT COUNT(pID) AS count2 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result2 = $db->query($count2);

	$getDescription = "SELECT pName, pDescription FROM Inventory WHERE pCategory = ".$category;
	$description = $db->query($getDescription);

	if($result2[0]['count2'] == $result1[0]['count1']+1)
	{
		array_push($dataArray, "Return From Repair Successful");
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}	
	else 
	{
		array_push($dataArray, "Return From Repair Failed");
		array_push($dataArray, $description);
		echo json_encode($dataArray);	
	}
}

// DECREMENT INVENTORY ITEM
else if($data['admin'] && ($_GET['action'] == "repair" || $_GET['action'] == "remove") && !empty($_GET['category']))
{
	$dataArray = array();
	$category = $_GET['category'];
	$action = $_GET['action'];

	$count1 = "SELECT COUNT(pID) AS count1 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result1 = $db->query($count1);

	$getMax = "SELECT MAX(pID) AS maxID FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$max = $db->query($getMax);

	if($action == "remove")
	{
		$flagAsInactive = "UPDATE Inventory SET pStatus = 'i' WHERE pID = ".$max[0]['maxID'];
		$db->query($flagAsInactive);
	}
	else if($action == "repair")
	{
		$flagAsRepair = "UPDATE Inventory SET pStatus = 'r' WHERE pID = ".$max[0]['maxID'];
		$db->query($flagAsRepair);
	}

	$count2 = "SELECT COUNT(pID) AS count2 FROM Inventory WHERE pCategory = ".$category." AND pStatus = 'v'";
	$result2 = $db->query($count2);

	$getDescription = "SELECT pName, pDescription FROM Inventory WHERE pCategory = ".$category;
	$description = $db->query($getDescription);

	if($result2[0]['count2']+1 == $result1[0]['count1'])
	{
		array_push($dataArray, "Decrementation Successful");
		array_push($dataArray, $action);
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}	
}

else if($data['admin'] && $_GET['action'] == "addNewInventoryItem" && !empty($_GET['category']) && !empty($_GET['name']) && 
	!empty($_GET['description']) && !empty($_GET['lifespan']) && !empty($_GET['date']) && !empty($_GET['cost']) && !empty($_GET['reservable']))
{
	$dataArray = array();
	$category = $_GET['category'];
	$name = $_GET['name'];
	$description = $_GET['description'];
	$lifespan = $_GET['lifespan'];
	$date = $_GET['date'];
	$cost = $_GET['cost'];
	$reservable = $_GET['reservable'];

	$insert = "INSERT INTO Inventory VALUES (NULL,".$category.",'".$name."','".$description."',".$lifespan.",'".$date."','".
	 		$cost."',".$reservable.",'v')";
	$db->query($insert);

	$verifyInsert = "SELECT * FROM Inventory WHERE pCategory = ".$category;
	$result = $db->query($verifyInsert);
	if($result > 0)
	{
		array_push($dataArray, "Inventory Addition Successful");
		array_push($dataArray, $result);
		echo json_encode($dataArray);
	}
	else
	{
		array_push($dataArray, "Inventory Addition Failed");
		array_push($dataArray, $name);
		array_push($dataArray, $description);
		echo json_encode($dataArray);
	}
}
else if($data['admin'] && $_GET['action'] == "addToPackageItems" && !empty($_GET['category']) && !empty($_GET['packageID']))
{
	$dataArray = array();
	$category = $_GET['category'];
	$package = $_GET['packageID'];
	$insert = "INSERT INTO PackageItems VALUES(NULL,".$package.",".$category.")";
	$db->query($insert);

	$verify = "SELECT * FROM PackageItems WHERE packageID=".$package." AND categoryID=".$category;
	$result = $db->query($verify);

	if(count($result) > 0)
	{
		array_push($dataArray, "Add To Package Items Successful");
		echo json_encode($dataArray);
	}
	else
	{
		array_push($dataArray, "Add To Package Items Failed");
		echo json_encode($dataArray);
	}
}







