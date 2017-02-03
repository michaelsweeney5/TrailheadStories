<?php
session_start();
if(empty($_SESSION['user']))
{
    header("Location: inventory.php");
    die("Redirecting to Inventory");
}

require_once('common.php');

if($data['admin'] && $_GET['action'] == "displayPrices" && !empty($_GET['category']))
{
	$dataArray = array();
	$category = $_GET['category'];

	$prices = "SELECT * FROM Prices WHERE priceCategory = " . $category;
	$result = $db->query($prices);

	if($result > 0)
	{
		array_push($dataArray, "Show Prices");
		array_push($dataArray, $result);
		echo json_encode($dataArray);
	}
	else
	{
		array_push($dataArray, "No Prices");
		echo json_encode($dataArray);
	}
}