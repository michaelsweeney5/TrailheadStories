<?php
	
	require_once('include/common.php');

	$m = new Mustache_Engine([
    	'pragmas' => [Mustache_Engine::PRAGMA_BLOCKS],
    	'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/views'),
	]);

	if(!empty($_GET['product']))
	{
		echo $_GET['product'] . "<br />";

		$productAvailability = "SELECT Carts.startDate, Carts.endDate, Inventory.pName, Inventory.pDescription 
								FROM Carts JOIN ItemsInCarts ON Carts.cartID = ItemsInCarts.cartID 
								JOIN Inventory ON ItemsInCarts.productID = Inventory.pCategory
								WHERE Inventory.pCategory = ".$_GET['product'];

		// RENDER ALL UNAVAILABLE DATES IF QTY. IS 0.  OTHERWISE, IT'S AVAILABLE.
    	$data['Inventory'] = $db->query($productAvailability);
	}
	else if(empty($_GET['product']))
	{
		echo "How did you get here??<br />";
	}

	$tpl = $m->loadTemplate('product_availability');
  	echo $tpl->render($data);
?>