<?php
// USE PDO
//ini_set('display_errors', 'On');


session_start();
if(empty($_SESSION['user']))
{
    header("Location: index.php");
    die("Redirecting to Log In");
}

require_once('include/common.php');

try {

// IF SOMEONE TAMPERS WITH ID OR DATE PARAMS (CHECK EXISTENCE OF CART DATA), KICK THEM OUT
if(!empty($_GET['id']) && !empty($_GET['startDate']) && !empty($_GET['endDate']))
{
    $checkCart = "SELECT * FROM Carts WHERE cartID = " . 
                  $_GET['id'] . 
                  " AND cartStartDate = '" . 
                  $_GET['startDate'] . 
                  "' AND cartEndDate = '" . 
                  $_GET['endDate'] . "'";

    $result = $db->query($checkCart);

    if(count($result) == 0)
    {
      header("Location: index.php");
      die("Redirecting to Log In");
    }
}

if($data['admin'] && empty($_GET['editData']) && empty($_GET['startDate']) && empty($_GET['endDate']))
{
    $data['AdminEditButton'] = "<a class='btn btn-sm btn-warning btn-raised' title='Open category and inventory editing' onclick='EngageAdminEditing()'>Open Data Editing</a>";
}
else if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
{
    $data['AdminEditButton'] = "<a class='btn btn-sm btn-warning btn-raised' title='Close category and inventory editing' onclick='DisengageAdminEditing()'>Close Data Editing</a>";
}

// ALL CATEGORY DATA
$categoryData = $db->orderBy('cDescription', 'asc')
                   ->get('Categories');

// SHOW ALL
if(empty($_GET) || (!empty($_GET['editData']) && empty($_GET['category']) && empty($_GET['availability']) && empty($_GET['search'])))
{
    // SETUP - GATHER ROOT BUTTONS DATA
    for($i = 0; $i < count($categoryData); $i++)
    {
        $categories[] = $categoryData[$i]['cCategory'];
        if($categoryData[$i]['cParent'] == 0) $data['Test'][] = $categoryData[$i];
    }

    // MENU ITEMS
    if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true") $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?editData=true&category=";
    else $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?category=";

    for($i = 0; $i < count($data['Test']); $i++)
    { 
        // REGULAR BUTTONS
        $data['Test'][$i]['parent'] = $data['Test'][$i]['cCategory']."'";

        // CATEGORY EDIT BUTTONS
        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
        {
            $data['Test'][$i]['NewCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Add new sub-category' onclick='NewCategory(".$data['Test'][$i]['cCategory'].")'><i class='zmdi zmdi-collection-plus zmdi-hc-lg'></i></a>";
            $data['Test'][$i]['EditCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category name' onclick='EditCategoryName(`".$data['Test'][$i]['cDescription']."`,".$data['Test'][$i]['cCategory'].",".$data['Test'][$i]['cParent'].")'><i class='zmdi zmdi-edit zmd-hc-lg'></i></a>";
            // ADD TO INVENTORY BUTTON (CHECKS FOR LEAF NODES)
            $checkForLeafNode = "SELECT * FROM Categories 
            WHERE cParent = " . $data['Test'][$i]['cCategory'];
            
            $leafNode = $db->query($checkForLeafNode);
            if(count($leafNode) == 0)
            {
                $notInInventory = "SELECT pID FROM Inventory WHERE pCategory = " . $data['Test'][$i]['cCategory'];
                $invResult = $db->query($notInInventory);
                if(count($invResult) == 0)
                {
                    $data['Test'][$i]['AddToInventoryButton'] = "<a class='btn btn-sm btn-primary btn-raised' title='Add new inventory item to this category' onclick='NewInventoryItem(".$data['Test'][$i]['cCategory'].")'><i class='zmdi zmdi-plus-1 zmdi-hc-lg'></i></a>";
                    $data['Test'][$i]['DeleteCategoryButton'] = "<a class='btn btn-sm btn-danger btn-raised' title='Delete this category' onclick='DeleteCategory(`".$data['Test'][$i]['cDescription']."`,".$data['Test'][$i]['cCategory'].",".$data['Test'][$i]['cParent'].")'><i class='zmdi zmdi-delete zmd-hc-lg'></i></a>";
                }
            }
        }
        else $data['Test'][$i]['SingleDatePicker'] = "<a class='btn btn-sm btn-info btn-raised' title='See availability for single date' onclick='SingleDatePicker(".$data['Test'][$i]['cCategory'].",`".$data['Test'][$i]['cDescription']."`)'><span class='glyphicon glyphicon-calendar'></span></a>";
    }
    $data['endingTag'] = "/a";

    // ALL INVENTORY ITEMS
    $allItems = "SELECT DISTINCT Inventory.pName, Inventory.pDescription, Inventory.pCategory
                 FROM Inventory INNER JOIN Categories
                 ON Inventory.pCategory = Categories.cCategory 
                 WHERE pStatus = 'v' AND Categories.cCategory IN (" . implode(',', $categories) . ")
                 ORDER BY Inventory.pName ASC";

    $data['Inventory'] = $db->query($allItems);

    // INVENTORY AVAILABILITY BUTTON
    for($i = 0; $i < count($data['Inventory']); $i++)
    {     
        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true") $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?editData=true&availability=".$data['Inventory'][$i]['pCategory']."'>";
        else $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?availability=".$data['Inventory'][$i]['pCategory']."'>"; 
        $data['Inventory'][$i]['Label'] = "Availability";
        $data['Inventory'][$i]['Ending'] = "</a>";

        // INVENTORY EDIT BUTTONS
        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
        {
            $data['Inventory'][$i]['Increment'] = "<a class='btn btn-sm btn-info btn-raised' title='Add a single item or return one from repair' onclick='IncrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-plus zmdi-hc-lg'></i></a>";
            $data['Inventory'][$i]['Decrement'] = "<a class='btn btn-sm btn-info btn-raised' title='Remove a single item or flag one for repair' onclick='DecrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-minus zmdi-hc-lg'></i></a>";
            $data['Inventory'][$i]['EditPrices'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category prices' onclick='EditCategoryPrices(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmd-hc-lg'></i></a>";
            $data['Inventory'][$i]['EditPackages'] = "<a class='btn btn-sm btn-info btn-raised' title='Add to package' onclick='EditPackages(".$data['Inventory'][$i]['pCategory'].",`".$data['Inventory'][$i]['pDescription']."`)'><i class='zmdi zmdi-card-giftcard zmdi-hc-lg'></i></a>";
            $data['Inventory'][$i]['DeleteTag'] = "<a class='btn btn-sm btn-danger btn-raised' title='Delete this inventory item' onclick='DeleteInventoryItem(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-delete zmdi-hc-lg'></i></a>";
        }
        else $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";
    }
}

// IF CATEGORY SELECTED
else if ((!empty($_GET['category']) || !empty($_GET['availability']) || !empty($_GET['action'])) && empty($_GET['search']))
{
    if(!empty($_GET['category'])) $category = $_GET['category'];
    if(!empty($_GET['availability'])) $category = $_GET['availability'];
    if($category == NULL) $category = 0;

    $root = $category;

    $queryResult = array();
    $menuResult = array();


    // ***** SETUP BUTTON DATA *****

    // QUERY NEW (SUBCATEGORY) BUTTONS
    $newButtons = "SELECT * FROM Categories WHERE Categories.cParent = " . $category . " ORDER BY Categories.cDescription";
    $menuResult = $db->query($newButtons);

    // GET ALL BUTTON DATA UP TO CURRENT
    function GetAllButtons($categoryData, $category, $level, &$menuResult)
    {
      for($i = 0; $i < count($categoryData); $i++)
      {
        if($categoryData[$i]['cCategory'] == $category && !is_null($categoryData[$i]['cParent']))
        {
          for($j = 0; $j < count($categoryData); $j++)
          {
            if($categoryData[$j]['cParent'] == $categoryData[$i]['cParent'])
            {
              //echo str_repeat("-", $level) . " " . $categoryData[$j]['cCategory'] . " " . $categoryData[$j]['cDescription'] . " BUTTON!! <br />"; 
              $menuResult[] = $categoryData[$j];
            }
          }
          GetAllButtons($categoryData, $categoryData[$i]['cParent'], $level+1, $menuResult);
        }
      }
    }
    GetAllButtons($categoryData, $root, $level, $menuResult);

    // SETUP BUTTON DISPLAY
    $newResult = array();
    $levels = array();
    
    function CreateMenu($menuResult, $parent, $level, &$newResult, &$levelStorage)
    {
      foreach($menuResult as $mR)
      {
        if($mR['cParent'] == $parent)
        {
          $newResult[] = $mR;
          $levelStorage[] = $level;
          CreateMenu($menuResult, $mR['cCategory'], $level+1, $newResult, $levelStorage);
        }
      }
    }
    CreateMenu($menuResult, 0, $level, $newResult, $levelStorage);


    // ***** CREATE BUTTONS *****

    // ACTIVE CART PARAMS
    if(!empty($_GET['action']) && $_GET['action'] == "reserve" && empty($_GET['search']))
    {
      for($i = 0; $i < count($newResult); $i++)
      {
        $data['Test'][$i]['action'] = "&action=".$_GET['action'];
        $data['Test'][$i]['id'] = "&id=".$_GET['id'];
        $data['Test'][$i]['startDate'] = "&startDate=".$_GET['startDate'];
        $data['Test'][$i]['endDate'] = "&endDate=".$_GET['endDate'];
      }
    }

    // RENDER ALL CATEGORY BUTTONS
    if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true") $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?editData=true&category=";
    else $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?category=";
    
    for($i = 0; $i < count($newResult); $i++)
    {
        $data['Test'][$i]['parent'] = $newResult[$i]['cCategory'];
        $data['Test'][$i]['cDescription'] = $newResult[$i]['cDescription'];
        $data['Test'][$i]['indentation'] = str_repeat("-", $levelStorage[$i]*4);
        //$data['Test'][$i]['SingleDatePicker'] = "<a class='btn btn-sm btn-info btn-raised' title='See availability for single date' onclick='SingleDatePicker(".$newResult[$i]['cCategory'].",`".$newResult[$i]['cDescription']."`)'><span class='glyphicon glyphicon-calendar'></span></a>";

        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
        {
            $data['Test'][$i]['NewCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Add new sub-category' onclick='NewCategory(".$newResult[$i]['cCategory'].")'><i class='zmdi zmdi-collection-plus zmdi-hc-lg'></i></a>";
            $data['Test'][$i]['EditCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category name' onclick='EditCategoryName(`".$newResult[$i]['cDescription']."`,".$newResult[$i]['cCategory'].",".$newResult[$i]['cParent'].")'><i class='zmdi zmdi-edit zmd-hc-lg'></i></a>";
            
            // 'ADD TO INVENTORY / DELETE CATEGORY'
            $checkForLeafNode = "SELECT * FROM Categories WHERE cParent = " . $newResult[$i]['cCategory'];
            $leafNode = $db->query($checkForLeafNode);

            if(count($leafNode) == 0)
            {
                $notInInventory = "SELECT pID FROM Inventory WHERE pCategory = " . $newResult[$i]['cCategory'];
                $invResult = $db->query($notInInventory);

                if(count($invResult) == 0)
                {
                    $data['Test'][$i]['AddToInventoryButton'] = "<a class='btn btn-sm btn-primary btn-raised' title='Add new inventory item to this category' onclick='AddInventory(".$newResult[$i]['cCategory'].")'><i class='zmdi zmdi-plus-1 zmdi-hc-lg'></i></a>";
                    $data['Test'][$i]['DeleteCategoryButton'] = "<a class='btn btn-sm btn-danger btn-raised' title='Delete this category' onclick='DeleteCategory(`".$newResult[$i]['cDescription']."`,".$newResult[$i]['cCategory'].",".$newResult[$i]['cParent'].")'><i class='zmdi zmdi-delete zmd-hc-lg'></i></a>";
                }
            }
        }
        else $data['Test'][$i]['SingleDatePicker'] = "<a class='btn btn-sm btn-info btn-raised' title='See availability for single date' onclick='SingleDatePicker(".$newResult[$i]['cCategory'].",`".$newResult[$i]['cDescription']."`)'><span class='glyphicon glyphicon-calendar'></span></a>";
    }
    $data['endingTag'] = "/a";


    // ***** GET INVENTORY STUFF *****
    function GetInventoryCategories($categoryData, $parent, &$queryResult)
    {
      foreach($categoryData as $arr)
      {
        if($arr['cParent'] == $parent)
        {
          $queryResult[] = $arr['cCategory'];
          GetInventoryCategories($categoryData, $arr['cCategory'], $queryResult);
        }
      }
    }

    GetInventoryCategories($categoryData, $root, $queryResult);

    // Handles Error 500 when querying empty $result
    if(count($queryResult) == 0)
    {
      $queryResult[] = $category;
    }

    if(!empty($_GET['category']))
    {
        $sub1Items = "SELECT DISTINCT Inventory.pName, Inventory.pDescription, Inventory.pCategory
                      FROM Inventory INNER JOIN Categories
                      ON Inventory.pCategory = Categories.cCategory
                      AND Categories.cCategory IN (" . implode(',', $queryResult) . ")
                      ORDER BY Inventory.pName ASC";
        
          $data['Inventory'] = $db->query($sub1Items);
       

        if(!empty($_GET['action']) && $_GET['action'] == "reserve")
        {
            for($i = 0; $i < count($data['Inventory']); $i++)
            {
              $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' href='inventory.php?availability=".
                $data['Inventory'][$i]['pCategory']."&action=".
                $_GET['action']."&id=".$_GET['id']."&startDate=".
                $_GET['startDate']."&endDate=".$_GET['endDate']."'>"; 
              $data['Inventory'][$i]['Label'] = "Availability";
              $data['Inventory'][$i]['Ending'] = "</a>";
              $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";

            }
        }
        else
        {
            for($i = 0; $i < count($data['Inventory']); $i++)
            {
                if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true") $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?editData=true&availability=".$data['Inventory'][$i]['pCategory']."'>";
                else $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?availability=".$data['Inventory'][$i]['pCategory']."'>";
                $data['Inventory'][$i]['Label'] = "Availability";
                $data['Inventory'][$i]['Ending'] = "</a>";

                if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
                {
                    $data['Inventory'][$i]['Increment'] = "<a class='btn btn-sm btn-info btn-raised' title='Add a single item or return one from repair' onclick='IncrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-plus zmdi-hc-lg'></i></a>";
                    $data['Inventory'][$i]['Decrement'] = "<a class='btn btn-sm btn-info btn-raised' title='Remove a single item or flag one for repair' onclick='DecrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-minus zmdi-hc-lg'></i></a>";
                    $data['Inventory'][$i]['EditPrices'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category prices' onclick='EditCategoryPrices(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmd-hc-lg'></i></a>";
                    $data['Inventory'][$i]['EditPackages'] = "<a class='btn btn-sm btn-info btn-raised' title='Add to package' onclick='EditPackages(".$data['Inventory'][$i]['pCategory'].",`".$data['Inventory'][$i]['pDescription']."`)'><i class='zmdi zmdi-card-giftcard zmdi-hc-lg'></i></a>";
                    $data['Inventory'][$i]['DeleteTag'] = "<a class='btn btn-sm btn-danger btn-raised' title='Delete this inventory item' onclick='DeleteInventoryItem(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-delete zmdi-hc-lg'></i></a>";
                }
                else $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";
            }
        }
    }

    // RENDER "Add To Cart" OR "Calendar" BUTTONS
    else if(!empty($_GET['availability']) && empty($_GET['search']))
    {
        if(!empty($_GET['startDate']) && !empty($_GET['endDate']))
        {
            // SHOW ONLY DATA THAT IS NOT CURRENTLY PART OF AN INVOICE FOR THE DATES SPECIFIED (CART IN SESSION)
            $sub1Items = "SELECT Inventory.pID, Inventory.pName, Inventory.pDescription, Inventory.pCategory
                          FROM Inventory INNER JOIN Categories
                          ON Inventory.pCategory = Categories.cCategory
                          WHERE Inventory.pReservable = TRUE
                          AND Inventory.pStatus = 'v'
                          AND Categories.cCategory = ".$category."
                          AND Inventory.pID NOT IN 
                          (
                              SELECT InvoiceItems.productID
                              FROM InvoiceItems
                              JOIN Invoices ON Invoices.invoiceID = InvoiceItems.invoiceID
                              WHERE InvoiceItems.productID = Inventory.pID
                              AND Invoices.invoiceEndDate BETWEEN '".$_GET['startDate']."' AND '".$_GET['endDate']."'
                              OR Invoices.invoiceStartDate BETWEEN '".$_GET['startDate']."' AND '".$_GET['endDate']."'
                          )
                          ORDER BY Inventory.pDescription ASC";

            $data['Inventory'] = $db->query($sub1Items);

            if(count($data['Inventory']) == 0)
            {
                $data['EmptyResultMessage'] = "This item is not available.";
            }
            else
            {
                for($i = 0; $i < count($data['Inventory']); $i++)
                {
                    $insertToCartButton = "insertToCartButton".$i;
                          
                    $data['Inventory'][$i]['AddToCartButton'] = "<form name='form1' action='' method='post'><input type='button' id='".$insertToCartButton."' name='insertToCartButton' value='Add To Cart' onclick='AddToCart(".
                    $data['Inventory'][$i]['pID'].",".$i.");' />";
                    $data['Inventory'][$i]['Response'] = "<br /><div id='response".$i."'></div>";
                }
            }
        }
        else
        {
            // SHOW CALENDAR HERE
            $sub1Items = "SELECT Inventory.pID, Inventory.pName, Inventory.pDescription, Inventory.pCategory
                          FROM Inventory INNER JOIN Categories
                          ON Inventory.pCategory = Categories.cCategory
                          WHERE Inventory.pStatus = 'v' 
                          AND Categories.cCategory = " . $category . "
                          ORDER BY Inventory.pDescription ASC";

            $data['Inventory'] = $db->query($sub1Items);

            if(count($data['Inventory']) == 0)
            {
                $data['EmptyResultMessage'] = "This item is not available.";
            }
            else
            {
                for($i = 0; $i < count($data['Inventory']); $i++)
                {
                    $insertToCartButton = "insertToCartButton".$i;
                          
                    $data['Inventory'][$i]['calendarButton'] = "<button type='button' data-toggle='modal' data-target='#calendarModal' class='btn btn-default btn-sm' id='calendarButton' onclick='ViewCalendar(".$data['Inventory'][$i]['pID'].",".$data['Inventory'][$i]['pCategory'].");'><span class='glyphicon glyphicon-calendar'></span> Calendar </button>"; 
                }
            }
        }
    }

    // ROOT BUTTON (ALL INVENTORY) ??
    else if(empty($_GET['category']) && !empty($_GET['id']) && !empty($_GET['startDate']) && !empty($_GET['endDate']))
    {
        $allItems = "SELECT DISTINCT Inventory.pName, Inventory.pDescription, Inventory.pCategory
                     FROM Inventory
                     ORDER BY Inventory.pName ASC";

        $data['Inventory'] = $db->query($allItems);

        // ADD ONE FOR SEARCH
        for($i = 0; $i < count($data['Inventory']); $i++)
        {
            $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' href='inventory.php?availability=".
              $data['Inventory'][$i]['pCategory']."&action=".
              $_GET['action']."&id=".$_GET['id']."&startDate=".
              $_GET['startDate']."&endDate=".$_GET['endDate']."'>"; 
            $data['Inventory'][$i]['Label'] = "Availability";
            $data['Inventory'][$i]['Ending'] = "</a>";
            $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";
        }
    }
}


// ***** SHOW SEARCH RESULTS *****
else if(!empty($_GET['search']))
{
    // SETUP
    foreach($categoryData as $cData)
    { 
      $categories[] = $cData['cCategory'];
      if($cData['cParent'] == 0) $data['Test'][] = $cData;
    }

    // MENU ITEMS
    $data['aTag'] = array();

    if(!empty($_GET['editData']) && $_GET['editData'] == "true") $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?editData=true&category=";
    else $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?category=";

    for($i = 0; $i < count($data['Test']); $i++)
    {
        $data['Test'][$i]['parent'] = $data['Test'][$i]['cCategory'];
        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
        {
            $data['Test'][$i]['NewCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Add new sub-category' onclick='NewCategory(".$data['Test'][$i]['cCategory'].")'><i class='zmdi zmdi-collection-plus zmdi-hc-lg'></i></a>";
            $data['Test'][$i]['EditCategoryButton'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category name' onclick='EditCategoryName(`".$data['Test'][$i]['cDescription']."`,".$data['Test'][$i]['cCategory'].",".$data['Test'][$i]['cParent'].")'><i class='zmdi zmdi-edit zmd-hc-lg'></i></a>";
        }
        else $data['Test'][$i]['SingleDatePicker'] = "<a class='btn btn-sm btn-info btn-raised' title='See availability for single date' onclick='SingleDatePicker(".$data['Test'][$i]['cCategory'].",`".$data['Test'][$i]['cDescription']."`)'><span class='glyphicon glyphicon-calendar'></span></a>";


        if(!empty($_GET['action']) && $_GET['action'] == "reserve")
        {
            $data['Test'][$i]['action'] = "&action=".$_GET['action'];
            $data['Test'][$i]['id'] = "&id=".$_GET['id'];
            $data['Test'][$i]['startDate'] = "&startDate=".$_GET['startDate'];
            $data['Test'][$i]['endDate'] = "&endDate=".$_GET['endDate'];
        }
    }
    $data['endingTag'] = "/a";

    $userData = $_GET['search'];

    // INVENTORY 
    if(ctype_alnum($userData)) 
    {
        $userSearch = "SELECT DISTINCT Inventory.pName, Inventory.pDescription, Inventory.pCategory 
                       FROM Inventory JOIN Categories ON Categories.cCategory = Inventory.pCategory
                       WHERE pStatus = 'v' AND pDescription LIKE '%".$userData."%'
                       OR pName LIKE '%".$userData."%'
                       OR pName = '" . $userData . "'
                       OR pDescription = '" . $userData . "' ORDER BY Inventory.pName ASC";

        $data['Inventory'] = $db->query($userSearch);

        if(count($data['Inventory']) == 0)
        {
          $data['EmptyResultMessage'] = "No Results.";
        }
        else
        {
            if($_GET['action'] == 'reserve')
            {
                for($i = 0; $i < count($data['Inventory']); $i++)
                {
                  $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' href='inventory.php?availability=".
                    $data['Inventory'][$i]['pCategory']."&action=".
                    $_GET['action']."&id=".$_GET['id']."&startDate=".
                    $_GET['startDate']."&endDate=".$_GET['endDate']."'>"; 
                  $data['Inventory'][$i]['Label'] = "Availability";
                  $data['Inventory'][$i]['Ending'] = "</a>";
                  $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";
                }
            }
            else
            {
                for($i = 0; $i < count($data['Inventory']); $i++)
                {
                    if(!empty($_GET['editData']))
                    {
                        if(!empty($_GET['editData']) && $_GET['editData'] == "true") $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?editData=true&availability=".$data['Inventory'][$i]['pCategory']."'>"; 
                        else $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?availability=".$data['Inventory'][$i]['pCategory']."'>"; 
                       
                        $data['Inventory'][$i]['Label'] = "Availability";
                        $data['Inventory'][$i]['Ending'] = "</a>";

                        if($data['admin'] && !empty($_GET['editData']) && $_GET['editData'] == "true")
                        {
                            $data['Inventory'][$i]['Increment'] = "<a class='btn btn-sm btn-info btn-raised' title='Add a single item or return one from repair' onclick='IncrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-plus zmdi-hc-lg'></i></a>";
                            $data['Inventory'][$i]['Decrement'] = "<a class='btn btn-sm btn-info btn-raised' title='Remove a single item or flag one for repair' onclick='DecrementInventoryItem(".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-minus zmdi-hc-lg'></i></a>";
                            $data['Inventory'][$i]['EditPrices'] = "<a class='btn btn-sm btn-info btn-raised' title='Edit category prices' onclick='EditCategoryPrices(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmd-hc-lg'></i></a>";
                            $data['Inventory'][$i]['EditPackages'] = "<a class='btn btn-sm btn-info btn-raised' title='Add to package' onclick='EditPackages(".$data['Inventory'][$i]['pCategory'].",`".$data['Inventory'][$i]['pDescription']."`)'><i class='zmdi zmdi-card-giftcard zmdi-hc-lg'></i></a>";
                            $data['Inventory'][$i]['DeleteTag'] = "<a class='btn btn-sm btn-danger btn-raised' title='Delete this inventory item' onclick='DeleteInventoryItem(`".$data['Inventory'][$i]['pName']."`,`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-delete zmdi-hc-lg'></i></a>";
                        }
                    }
                    else
                    {                 
                        $data['Inventory'][$i]['AvailabilityTag'] = "<a class='btn btn-sm btn-success btn-raised' role='button' title='Check availability of this item' href='inventory.php?availability=".$data['Inventory'][$i]['pCategory']."'>"; 
                        $data['Inventory'][$i]['Label'] = "Availability";
                        $data['Inventory'][$i]['Ending'] = "</a>";
                        $data['Inventory'][$i]['SeePrices'] = "<a class='btn btn-sm btn-info btn-raised' title='See prices for this item' onclick='ShowPrices(`".$data['Inventory'][$i]['pDescription']."`,".$data['Inventory'][$i]['pCategory'].")'><i class='zmdi zmdi-money zmdi-hc-lg'></i></a>";

                    }
                }
            }
        }
    }
    else
    {
        header("Location: inventory.php");
        die("Redirecting to Log In");
    }
}

  // SHOW EMPTY SEARCH
else if(empty($_GET['search']))
{
    // SETUP
    foreach($categoryData as $cData)
    { 
      $categories[] = $cData['cCategory'];
      if($cData['cParent'] == 0) $data['Test'][] = $cData;
    }

    // MENU ITEMS
    $data['aTag'] = array();


    $data['aTag'] = "a class='btn btn-sm btn-success btn-raised' href='inventory.php?category=";
    for($i = 0; $i < count($data['Test']); $i++) $data['Test'][$i]['parent'] = $data['Test'][$i]['cCategory']."'";
    $data['endingTag'] = "/a";

    // No Result Message
    $data['EmptyResultMessage'] = "No Results.";
}

    $tpl = $m->loadTemplate('inventory');
    echo $tpl->render($data);

} catch (Exception $e) {
    session_destroy();
    header("Location: index.php");
    die("Redirecting to Log In"); }


