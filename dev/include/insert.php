<?php
//ini_set('display_errors', 'On');
require_once('common.php');

// ADDING WITH CART IN SESSION
if(!empty($_GET['startDate']) && !empty($_GET['endDate']))
{
	$cartID = $_GET['id'];
	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	$productID = $_GET['product'];

	// In case they press "back" key and try to re-add same item.
	$check = "SELECT * FROM CartItems WHERE cartID=".$cartID;

	$result = $db->query($check);

	$alreadyExists = false;

	for($i = 0; $i < count($result); $i++)
	{
		if($result[$i]['productID'] == $productID)
		{
			$alreadyExists = true;
		}
	}

	if(!$alreadyExists)
	{
		$insert = "INSERT INTO CartItems VALUES('NULL',".$cartID.",".$productID.")";

		$db->query($insert);

		echo "<b>This item (#".$productID.") has been added to cart #". $cartID;
		echo " and will be reserved between ".$startDate." and ".$endDate."</b>";
	}
	else
	{
		echo "Item #" . $productID . " is already in cart #" . $cartID . "!";
	}
}

//CREATING NEW CART FROM GENERAL INVENTORY ?
else if(!empty($_GET['id']) && !empty($_GET['product']))
{
	$cartID = $_GET['id'];
	$productID = $_GET['product'];

	$insert = "INSERT INTO CartItems VALUES('NULL',".$cartID.",".$productID.")";

	$db->query($insert);

		//echo "<b>This item (#".$productID.") has been added to cart #". $cartID;
		//echo " and will be reserved between ".$startDate." and ".$endDate."</b>";
}


?>