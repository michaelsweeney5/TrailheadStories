
var globalProduct;
var rentalDate;
var returnDate;
var category;

// This starts the section copied over from working carts.js file
$(document).on("click", "#buildNewCartButton", function(e) {
    if(rentalDate != null && returnDate != null) {
        $("#cartListModal").modal('toggle');
        console.log(rentalDate);
        if(rentalDate != null && returnDate != null) {
            var newRentalDate = rentalDate.slice(6) + "-" + rentalDate.slice(0, 2) + "-" + rentalDate.slice(3, 5);
            var newReturnDate = returnDate.slice(6) + "-" + returnDate.slice(0, 2) + "-" + returnDate.slice(3, 5);
        }
        document.getElementById("cartStartDate").value = newRentalDate;
        document.getElementById("cartEndDate").value = newReturnDate;
        document.getElementById("hiddenProductID").value = globalProduct;
        document.getElementById("hiddenCategoryID").value = category;
        $("#buildCartModal").modal();
    }
});
// This ends the section copied over from working carts.js file

function Refresh(category)
{
    window.location.href = "inventory.php?editData=true&category="+category;
}

function Search(userString)
{
    var currentURL = window.location.href;
    var newURL;
    var indexOfQM = currentURL.indexOf("?");
    var ampersandIndex = currentURL.indexOf("&");    
    var searchParamIndex = currentURL.indexOf("search=");
    var dataEditParamIndex = currentURL.indexOf("editData=");
    var categoryParamIndex = currentURL.indexOf("category=");
    var availabilityParamIndex = currentURL.indexOf("availability=");
    var actionReserveParamIndex = currentURL.indexOf("action=");

    // Prevent SQL injection attempts
    var proceed = false;
    for(var i = 0; i < userString.length; i++)
    {
        if(userString[i] == "'" || userString[i] == "`")
        {
            proceed = false;
            i = userString.length;
            alert("Error:  Invalid characters!");
        }
        else proceed = true;
    }

    if(proceed)
    {
        if(indexOfQM > -1)
        {
            if(searchParamIndex > -1)
            {
                console.log("Search exists");
                if(ampersandIndex > -1)
                {
                    var searchParam = currentURL.substring(searchParamIndex, ampersandIndex);
                    newURL = currentURL.replace(searchParam, "search="+userString);
                    console.log("QM, search, and ampersand exist");
                }
                else
                {
                    var searchParam = currentURL.substring(searchParamIndex, currentURL.length);
                    newURL = currentURL.replace(searchParam, "search="+userString);
                    console.log("QM and search exist, ampersand does not");                
                }
            }
            else if(searchParamIndex == -1)
            {
                console.log("Search does not exist");
                if(dataEditParamIndex > -1)
                {
                    var tempURL = currentURL.substring(0, dataEditParamIndex);
                    var paramList = currentURL.substring(dataEditParamIndex, currentURL.length);
                    newURL = tempURL+"search="+userString+"&"+paramList;
                    console.log("QM and editData exists");
                }
                else if(categoryParamIndex > -1)
                {
                    if(ampersandIndex > -1)
                    {
                        var categoryParam = currentURL.substring(categoryParamIndex, ampersandIndex);
                        newURL = currentURL.replace(categoryParam, "search="+userString);
                        console.log("QM, category, and ampersand exist");
                    }
                    else
                    {
                        var categoryParam = currentURL.substring(categoryParamIndex, currentURL.length);
                        newURL = currentURL.replace(categoryParam, "search="+userString);
                        console.log("QM and category exist. Ampersand does not");
                    }
                }
                else if(availabilityParamIndex > -1)
                {
                    if(ampersandIndex > -1)
                    {
                        var availabilityParam = currentURL.substring(availabilityParamIndex, ampersandIndex);
                        newURL = currentURL.replace(availabilityParam, "search="+userString);
                        console.log("QM, availability, and ampersand exist");
                    }
                    else
                    {
                        var availabilityParam = currentURL.substring(availabilityParamIndex, currentURL.length);
                        newURL = currentURL.replace(availabilityParam, "search="+userString);
                        console.log("QM and availability exist.  Ampersand does not");
                    }
                }
                else if(actionReserveParamIndex > -1)
                {
                    var tempURL = currentURL.substring(0, actionReserveParamIndex);
                    var paramList = currentURL.substring(actionReserveParamIndex, currentURL.length);
                    newURL = tempURL+"search="+userString+"&"+paramList;
                    console.log("QM and action exists.");
                }
            }
        }
        else newURL = currentURL+"?search="+userString;
        console.log("New: " + newURL);
        window.location.href = newURL;
    }
}

function EngageAdminEditing()
{
    var currentURL = window.location.href;
    var newURL;
    var categoryParamIndex = currentURL.indexOf("category=");
    var availabilityParamIndex = currentURL.indexOf("availability=");

    if(categoryParamIndex > -1) 
    {
        var categoryParam = currentURL.substring(categoryParamIndex, currentURL.length);
        newURL = "inventory.php?editData=true&"+categoryParam;
    }
    else if(availabilityParamIndex > -1)
    {
        var availabilityParam = currentURL.substring(availabilityParamIndex, currentURL.length);
        newURL = "inventory.php?editData=true&"+availabilityParam;
    }
    else newURL = "inventory.php?editData=true";

    window.location.href = newURL;
}

function DisengageAdminEditing()
{
    var currentURL = window.location.href;
    var newURL;
    var categoryParamIndex = currentURL.indexOf("category=");
    var availabilityParamIndex = currentURL.indexOf("availability=");

    if(categoryParamIndex > -1) 
    {
        var categoryParam = currentURL.substring(categoryParamIndex, currentURL.length);
        newURL = "inventory.php?"+categoryParam;
    }
    else if(availabilityParamIndex > -1)
    {
        var availabilityParam = currentURL.substring(availabilityParamIndex, currentURL.length);
        newURL = "inventory.php?"+availabilityParam;
    }
    else newURL = "inventory.php";

    window.location.href = newURL;
}

function AddToCart(productID, buttonID)
{
  var path = window.location.href.split('?');
  var url = "../include/insert.php?" + path[1] + "&product=" + productID;
  console.log(url);
  var xmlhttp=new XMLHttpRequest();

  xmlhttp.open("GET", url, false);
  xmlhttp.send(null);

  var button = "insertToCartButton" + buttonID;
  var response = "response" + buttonID;

  document.getElementById(button).disabled = true;
  document.getElementById(response).innerHTML=xmlhttp.responseText;
}

function AddToCartFromGetCarts(productID, cartID)
{
    var path = window.location.href.split('?');
    var url = "../include/insert.php?" + path[1] + "&id=" + cartID + "&product=" + productID;
    console.log(url);
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.open("GET", url, false);
    xmlhttp.send(null);
}

function NewCart()
{
    var url = "carts.php?product=" + globalProduct + "&startDate=" + rentalDate + "&endDate=" + returnDate;

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET", url, false);
    xmlhttp.send(null);

    console.log(url);
}

function VerifyDatePickerSelection()
{
    console.log("Verifying Date Picker Selection");
    var rentFieldValue = document.getElementById("rent").value;
    var returnFieldValue = document.getElementById("return").value;
   
    var rentalDateDay = rentFieldValue.substring(3, 5);
    var rentalDateMonth = rentFieldValue.substring(0, 2);
    var rentalDateYear = rentFieldValue.substring(6, 10);

    var returnDateDay = returnFieldValue.substring(3, 5);
    var returnDateMonth = returnFieldValue.substring(0, 2);
    var returnDateYear = returnFieldValue.substring(6, 10);


    var rentalDate = rentalDateYear + "-" + rentalDateMonth + "-" + rentalDateDay;
    var returnDate = returnDateYear + "-" + returnDateMonth + "-" + returnDateDay;

    document.getElementById("submitDatesButton").disabled = true;

    $.ajax({
        type: "GET",
        url: '../include/DatePicker.php?action=findConflicts&product='+globalProduct+'&startDate='+rentalDate+'&returnDate='+returnDate,
        success: function(data)
        {
            console.log(data);
            if(data[0] == "No Conflict")
            {
                document.getElementById("submitDatesButton").disabled = false;
                SubmitDates();
            }
            else if(data[0] == "Conflict")
            {
                alert("Error:  A conflict was found.  Please try again");
                document.getElementById("submitDatesButton").disabled = false;
            }
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });
}


function SubmitDates()
{
  console.log("Submitting Dates");
   $(function () {
     $('#calendarModal').modal('toggle');
     $('#cartListModal').modal('toggle');
   });

  var rentFieldValue = document.getElementById("rent").value;
  var returnFieldValue = document.getElementById("return").value;
  document.getElementById("buildNewCartButton").disabled = true;
  document.getElementById("cartDropDownList").innerHTML = "Searching for open carts...";
  
  rentalDate = rentFieldValue.replace('/', '-');
  rentalDate = rentalDate.replace('/', '-');
  returnDate = returnFieldValue.replace('/', '-');
  returnDate = returnDate.replace('/', '-');
  
  console.log("Submit Dates Reached for " + globalProduct);
  console.log("on " + rentalDate + " to " + returnDate);
  
  $.ajax({
        type: "GET",
        url: '../include/getCarts.php?product='+globalProduct+'&startDate='+rentalDate+'&returnDate='+returnDate,
        success: function(data)
        {
            console.log("Success!");
            console.log(data);
            if(data.length > 0)
            {
                document.getElementById("cartDropDownList").innerHTML = "";
                for(i = 0; i < data.length; i++)
                  document.getElementById("cartDropDownList").innerHTML += "<li><button type='button' onclick='AddToCartFromGetCarts(" + globalProduct + ", " + data[i]['cartID'] + ");'>" + data[i]['customerLastName'] + ", " + data[i]['customerFirstName'] + "<br />" + data[i]['customerPhone'] + "</button></li>";
            }
            else document.getElementById("cartDropDownList").innerHTML = "No open carts match this date range.";

            document.getElementById("buildNewCartButton").disabled = false;
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
            document.getElementById("buildNewCartButton").disabled = false;
        },
        dataType: "json"
    });
}

function EnableSubmitButton()
{
  var rentFieldValue = document.getElementById("rent").value;
  var returnFieldValue = document.getElementById("return").value;
  document.getElementById("submitDatesButton").disabled = true;

  if(rentFieldValue != "" && returnFieldValue != "")
  {
    if(returnFieldValue >= rentFieldValue) document.getElementById("submitDatesButton").disabled = false;
    else alert("Return date must be later than rental date!");
  }
}

function ViewCalendar(productID, categoryID)
{
    console.log("View Calendar reached for " + productID);
    category = categoryID;
    console.log("Category: " + category);

    document.getElementById("submitDatesButton").disabled = true;
    document.getElementById("rent").disabled = true;
    document.getElementById("return").disabled = true;

    $.ajax({
        type: "GET",
        url: '../include/DatePicker.php?product='+productID,
        success: function(data)
        {
            console.log("Success!");
            console.log(data);
            CreateCalendar(data);
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });
} 

function CreateCalendar(data)
{
    console.log("Creating calendar");
    console.log("Create Calendar Reached for item #" + data[0]);
    globalProduct = data[0];
    var thisYear = data[1];
    var thisMonth = data[2];
    var thisDay = data[3];

    var hyphens = [];
    var reservedDates = [];

    document.getElementById("rent").disabled = false;
    document.getElementById("return").disabled = false;
    console.log("Reserved Dates Length: " + reservedDates.length);
      if(data[4].length > 0) // One or more reservation dates for this item
      {
          for(k = 0; k < data[4].length; k++) // each element per object
          {  
              for(j = 0; j<10; j++) // each char
              {  
                  if(data[4][k]['invoiceStartDate'][j] == '-') hyphens.push(j);
              }

              var reservedStartYear = data[4][k]['invoiceStartDate'].substr(0, hyphens[0]);
              var reservedStartMonth = data[4][k]['invoiceStartDate'].substr(hyphens[0]+1, 2);
              var reservedStartDay = data[4][k]['invoiceStartDate'].substr(hyphens[1]+1, 2);

              var reservedEndYear = data[4][k]['invoiceEndDate'].substr(0, hyphens[0]);
              var reservedEndMonth = data[4][k]['invoiceEndDate'].substr(hyphens[0]+1, 2);
              var reservedEndDay = data[4][k]['invoiceEndDate'].substr(hyphens[1]+1, 2);

              // SLICE OFF 0 FOR DATEPICKER
              if(reservedStartMonth[0] == 0) reservedStartMonth = reservedStartMonth.slice(1, 2);
              if(reservedEndMonth[0] == 0) reservedEndMonth = reservedEndMonth.slice(1, 2);
              if(reservedStartDay[0] == 0) reservedStartDay = reservedStartDay.slice(1, 2);
              if(reservedEndDay[0] == 0) reservedEndDay = reservedEndDay.slice(1, 2);

              // GET ALL RESERVED DATES (CHECK FOR DATES WHERE END DATE IS AFTER 1st OF FOLLOWING MONTH)
               
              console.log("Start: " + reservedStartMonth + "-" + reservedStartDay + "-" + reservedStartYear);
              console.log("End: " + reservedEndMonth + "-" + reservedEndDay + "-"+ reservedEndYear);

              if(parseInt(reservedEndDay) < parseInt(reservedStartDay)) //If End Date is less than start date (end date is after 1st of following month/year)
              {
                  while(reservedStartDay <= 31) //This takes care of all months
                  {
                      reservedDates.push(reservedStartDay + "-" + reservedStartMonth + "-" + reservedStartYear); 
                      reservedStartDay++;
                  }
                  reservedStartDay = 1;

                  while(parseInt(reservedStartDay) <= parseInt(reservedEndDay))
                  {
                      reservedDates.push(reservedStartDay + "-" + reservedEndMonth + "-" + reservedEndYear); 
                      reservedStartDay++;
                  }
              }
              else if((parseInt(reservedEndDay) > parseInt(reservedStartDay)) && (parseInt(reservedEndMonth) > parseInt(reservedStartMonth) || parseInt(reservedStartMonth) == 12 && parseInt(reservedEndMonth) == 1 ))
              {
                  console.log("reservedEndDay > reservedStartDay and reservedEndMonth > reservedStartMonth OR...");
                  while(parseInt(reservedStartDay) <= 31) //This takes care of all months
                  {
                      reservedDates.push(reservedStartDay + "-" + reservedStartMonth + "-" + reservedStartYear); 
                      reservedStartDay++;
                  }

                  reservedStartDay = 1;

                  while(parseInt(reservedStartDay) <= parseInt(reservedEndDay))
                  {
                      reservedDates.push(reservedStartDay + "-" + reservedEndMonth + "-" + reservedEndYear); 
                      reservedStartDay++;
                  }
              }
              else // If End Date is greater than start date
              {
                  while(parseInt(reservedEndDay) >= parseInt(reservedStartDay))
                  {
                      reservedDates.push(reservedStartDay + "-" + reservedStartMonth + "-" + reservedStartYear);  
                      reservedStartDay++;
                  }
              }
          }
      }


    $(function(){
      $("#rent").datepicker("destroy");
    });
    $(function(){
      $("#return").datepicker("destroy");
    });

    $(function(){
        $("#rent").datepicker(
        {

            beforeShowDay: function (date) 
            {
                var currentDate = (date.getDate() + '-' + (date.getMonth()+1) + '-' + date.getFullYear());
                console.log("Reserved Dates Length: " + reservedDates.length);
                for(var y = 0; y < reservedDates.length; y++)
                {
                  console.log(currentDate +" vs. "+reservedDates[y] );
                  if($.inArray(currentDate, reservedDates) != -1) return [false];
                }

                if(date.getDate() >= thisDay && (date.getMonth()+1) == thisMonth && date.getFullYear() == thisYear)
                {
                    return [true, ''];
                }
                else
                {
                    if(date.getFullYear() < thisYear)
                    {
                        return [false, ''];
                    }
                    else if(date.getFullYear() > thisYear)
                    {
                        return [true, ''];
                    }
                    if(date.getMonth() < thisMonth)
                    {
                        return [false, ''];
                    }
                    else if(date.getMonth() > thisMonth)
                    {
                        return [true, ''];
                    }
                }

                return [true, ''];
            }
        });
    });

    $(function(){
        $("#return").datepicker(
        {
            beforeShowDay: function (date) 
            {
                var currentDate = (date.getDate() + '-' + (date.getMonth()+1) + '-' + date.getFullYear());
                
                for(var y = 0; y < reservedDates.length; y++)
                {
                   if($.inArray(currentDate, reservedDates) != -1) return [false];
                }

                if(date.getDate() >= thisDay && (date.getMonth()+1) == thisMonth && date.getFullYear() == thisYear)
                {
                    return [true, ''];
                }
                else
                {
                    if(date.getFullYear() < thisYear)
                    {
                        return [false, ''];
                    }
                    else if(date.getFullYear() > thisYear)
                    {
                        return [true, ''];
                    }
                    if(date.getMonth() < thisMonth)
                    {
                        return [false, ''];
                    }
                    else if(date.getMonth() > thisMonth)
                    {
                        return [true, ''];
                    }
                }
                return [true, ''];
            }
        });
    });
}

// CREATE NEW SUB CATEGORY
function NewCategory(productID)
{
    console.log("Adding New Category Below: "+productID);

    document.getElementById("hiddenCategoryForUpdate").value = productID;
    CheckForExistingInventory(productID);
}

function CheckForExistingInventory(productID)
{
    console.log("Search Inventory For pCategory = "+productID);
    document.getElementById("hiddenCategoryForMove").value = productID;
    $.ajax({
        type: "GET",
        url: '../include/updateInventory.php?action=checkCurrentCategory&category='+productID,
        success: function(data)
        {
            if(data[0] == "true")
            {
                console.log("Inventory Exists!");
                console.log(data);
                $("#moveCategoryModal").modal();
            }
            else if(data[0] == "false") $("#addCategoryModal").modal();
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });
}

function VerifyMoveCategoryInput(description, productID)
{
    console.log("Verifying move...");
    document.getElementById("submitNewMoveCategoryButton").disabled = true;
    var proceed = false;
    if(description == "")
    {  
        alert("Field Can Not Be Blank!");
        document.getElementById("submitNewMoveCategoryButton").disabled = false;
        proceed = false;
    }
    else
    {
        for(var i = 0; i < description.length; i++)
        {
            if(description[i] == "'" || description[i] == "`")
            {
                alert("Category name contains invalid characters!");
                document.getElementById("submitNewMoveCategoryButton").disabled = false;
                proceed = false;
                i = description.length;
            }
            else proceed = true;
        }
    }
    if(proceed)
    {
        $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=verifyCategory&description='+description.toLowerCase()+'&category='+productID,
            success: function(data)
            {
                console.log("Success!");
                console.log(data);
                if(data[0] == "Description Invalid")
                {
                    alert("Category Name '"+description.toLowerCase()+"' Already Exists!");
                    document.getElementById("submitNewMoveCategoryButton").disabled = false;
                }
                else if(data[0] == "Description Valid")
                {
                    console.log("Executing Move Category");
                    MoveCategory(data[1], data[2]);
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        });
    }
}

function MoveCategory(description, productID)
{
    console.log("Moving Category Item: '" + description + "' with parent category " + productID);
    $.ajax({
          type: "GET",
          url: '../include/updateInventory.php?action=moveCategory&description='+description+'&parent='+productID,
          success: function(data)
          {
              if(data[0] == "true")
              {
                  alert("Successfully Moved Category!");
                  console.log(data);
                  $("#moveCategoryModal").modal("toggle"); // Turns off
                  document.getElementById("submitNewMoveCategoryButton").disabled = false;
                  $("#addCategoryModal").modal();
              }
              else if(data[0] == "false")
              {
                  alert("An unexpected error occurred: "+data[1]+" was not successfully moved!")
              }
          }, 
          failure: function(data)
          {
              console.log("Error: " + data);
          },
          dataType: "json"
      });
}

function VerifyCategoryInput(description, productID)
{
    document.getElementById("submitNewCategoryButton").disabled = true;
    console.log("Return: " + description);
    console.log("Return2: " + productID);
    var proceed = false;
    if(description == "")
    {  
        alert("Field Can Not Be Blank!");
        document.getElementById("submitNewCategoryButton").disabled = false;
        proceed = false;
    }
    else
    {
        for(var i = 0; i < description.length; i++)
        {
            if(description[i] == "'" || description[i] == "`")
            {
                alert("Category name contains invalid characters!");
                document.getElementById("submitNewCategoryButton").disabled = false;
                proceed = false;
                i = description.length;
            }
            else proceed = true;
        }
    }
    
    if(proceed)
    {
        $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=verifyCategory&description='+description.toLowerCase()+'&category='+productID,
            success: function(data)
            {
                console.log("Success!");
                console.log(data);
                if(data == "Description Invalid")
                {
                    alert("Category Name '"+description.toLowerCase()+"' Already Exists!");
                    document.getElementById("submitNewCategoryButton").disabled = false;
                }
                else
                {
                    console.log("Executing...");
                    InsertNewCategory(data[1], data[2]);
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        });
    }
}

function InsertNewCategory(description, productID)
{
    console.log("Adding New Category Item: '" + description + "' with parent category " + productID);
    $.ajax({
          type: "GET",
          url: '../include/updateInventory.php?action=insertCategory&description='+description+'&parent='+productID,
          success: function(data)
          {
              console.log("Success!");
              console.log(data);
              $("#addCategoryModal").modal("toggle"); // Turns off
              document.getElementById("submitNewCategoryButton").disabled = false;
              Refresh(productID);
          }, 
          failure: function(data)
          {
              console.log("Error: " + data);
          },
          dataType: "json"
      });
}


// EDITING CATEGORIES
function EditCategoryName(name, category, parent)
{
    console.log("Editing category with current name: '"+name+"' at category #"+category+" with parent #"+parent);
    if(parent == 0) alert("You may not change the name of this category!");
    else
    {
        document.getElementById("editCategoryName").value = "";
        document.getElementById("hiddenCategoryForNameEdit").value = category;
        document.getElementById("hiddenParentForNameEdit").value = parent;
        document.getElementById("showCurrentCategoryForNameEdit").innerHTML = "Edit Name for Category: " + name;
        $("#editCategoryNameModal").modal();
    }
}
function VerifyCategoryNameChange(name, category, parent)
{
    document.getElementById("submitEditCategoryNameButton").disabled = true;

    var proceed = false;

    if(name == "")
    {
        alert("Category name can not be blank!");
        document.getElementById("submitEditCategoryNameButton").disabled = false;
        proceed = false;
    }
    else
    {
        for(var i = 0; i < name.length; i++)
        {
            if(name[i] == "'" || name[i] == "`")
            {
                alert("Category name contains invalid characters!");
                proceed = false;
                document.getElementById("submitEditCategoryNameButton").disabled = false;                    
                i = name.length;
            }
            else proceed = true;
        }
    }
    if(proceed) ExecuteCategoryNameEdit(name, category, parent);
}
function ExecuteCategoryNameEdit(name, category, parent)
{
    console.log("Execute Category Name Change: " + name + ", " + category + ", " + parent);
   
    $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=editCategory&name='+name+'&category='+category+'&parent='+parent,
            success: function(data)
            {
                console.log(data);
                if(data[0] == "Edit Successful")
                {
                    alert("Category "+category+" successfully updated to '"+name+"'");
                    $("#editCategoryNameModal").modal("toggle");
                    Refresh(category);
                }
                else if(data[0] == "Edit Failed")
                {
                    alert("Category edit failed. '"+name+"' already exists in this context.");
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        });
}
function EditCategoryPrices(name, description, category)
{
    console.log("Edit Category Prices: " + name + ", " + description + ", " + category);
    document.getElementById("submitEditCategoryPricesButton").disabled = false;
    document.getElementById("hiddenNameForPriceEdit").value = name;
    document.getElementById("hiddenDescriptionForPriceEdit").value = description;    
    document.getElementById("hiddenCategoryForPriceEdit").value = category;
    document.getElementById("showCurrentCategoryForPriceEdit").innerHTML = "Edit Price for Category: '" + name + " - " + description + "'";
    document.getElementById("studentOneDay").value = "";
    document.getElementById("studentTwoPlusDays").value = "";
    document.getElementById("facultyStaffAlumniOneDay").value = "";
    document.getElementById("facultyStaffAlumniTwoPlusDays").value = "";  
    document.getElementById("publicOneDay").value = "";
    document.getElementById("publicTwoPlusDays").value = "";
    $("#editCategoryPriceModal").modal();      
}
function NoNegatives()
{
    if(document.getElementById("studentOneDay").value < 0) document.getElementById("studentOneDay").value = 0;
    if(document.getElementById("studentTwoPlusDays").value < 0) document.getElementById("studentTwoPlusDays").value = 0;
    if(document.getElementById("facultyStaffAlumniOneDay").value < 0) document.getElementById("facultyStaffAlumniOneDay").value = 0;
    if(document.getElementById("facultyStaffAlumniTwoPlusDays").value < 0) document.getElementById("facultyStaffAlumniTwoPlusDays").value = 0; 
    if(document.getElementById("publicOneDay").value < 0) document.getElementById("publicOneDay").value = 0;        
    if(document.getElementById("publicTwoPlusDays").value < 0) document.getElementById("publicTwoPlusDays").value = 0;       
}
function VerifyCategoryPriceEdit(name, description, category, student1, student2, fcltyStfAlum1, fcltyStfAlum2, pub1, pub2)
{
    document.getElementById("submitEditCategoryPricesButton").disabled = true;

    var student1ok = false;
    var student2ok = false;
    var fcltyStfAlum1ok = false;
    var fcltyStfAlum2ok = false;
    var pub1ok = false;
    var pub2ok = false;
    console.log(name + "-" + description + ", " + category + ", " + student1 + ", " + student2 + ", " + fcltyStfAlum1 + ", " + fcltyStfAlum2 + ", " + pub1 + ", " + pub2);


        if(student1 == "")
        {
            alert("Student Price (1 day) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            student1ok = false;
        }
        else
        {
            for(var i = 0; i < student1.length; i++)
            {
                if(student1[i] == "'" || student1[i] == "`")
                {
                    alert("Student Price (1 day) contains invalid characters!");
                    student1ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = student1.length;
                }
                else student1ok = true;
            }
        }
        if(student2 == "")
        {
            alert("Student Price (2+ days) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            student2ok = false;
        }
        else
        {
            for(var i = 0; i < student2.length; i++)
            {
                if(student2[i] == "'" || student2[i] == "`")
                {
                    alert("Student Price (2+ days) contains invalid characters!");
                    student2ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = student2.length;
                }
                else student2ok = true;
            }
        }
        if(fcltyStfAlum1 == "")
        {
            alert("Faculty/Staff/Alumni (1 day) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            fcltyStfAlum1ok = false;
        }
        else
        {
            for(var i = 0; i < fcltyStfAlum1.length; i++)
            {
                if(fcltyStfAlum1[i] == "'" || fcltyStfAlum1[i] == "`")
                {
                    alert("Faculty/Staff/Alumni (1 day) contains invalid characters!");
                    fcltyStfAlum1ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = fcltyStfAlum1.length;
                }
                else fcltyStfAlum1ok = true;
            }
        }
        if(fcltyStfAlum2 == "")
        {
            alert("Faculty/Staff/Alumni (2+ days) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            fcltyStfAlum2ok = false;
        }
        else
        {
            for(var i = 0; i < fcltyStfAlum2.length; i++)
            {
                if(fcltyStfAlum2[i] == "'" || fcltyStfAlum2[i] == "`")
                {
                    alert("Faculty/Staff/Alumni (2+ days) contains invalid characters!");
                    fcltyStfAlum2ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = fcltyStfAlum2.length;
                }
                else fcltyStfAlum2ok = true;
            }
        }
        if(pub1 == "")
        {
            alert("Public (1 day) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            pub1ok = false;
        }
        else
        {
            for(var i = 0; i < pub1.length; i++)
            {
                if(pub1[i] == "'" || pub1[i] == "`")
                {
                    alert("Public (1 day) contains invalid characters!");
                    pub1ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = pub1.length;
                }
                else pub1ok = true;
            }
        }
        if(pub2 == "")
        {
            alert("Public (2+ days) can not be blank!");
            document.getElementById("submitEditCategoryPricesButton").disabled = false;
            pub2ok = false;
        }
        else
        {
            for(var i = 0; i < pub2.length; i++)
            {
                if(pub2[i] == "'" || pub2[i] == "`")
                {
                    alert("Public (2+ days) contains invalid characters!");
                    pub2ok = false;
                    document.getElementById("submitEditCategoryPricesButton").disabled = false;
                    i = pub2.length;
                }
                else pub2ok = true;
            }
        }

    if(student1ok && student2ok && fcltyStfAlum1ok && fcltyStfAlum2ok && pub1ok && pub2ok) 
    {  
        ExecuteCategoryPriceEdit(name, description, category, student1, student2, fcltyStfAlum1, fcltyStfAlum2, pub1, pub2);
    }
}
function ExecuteCategoryPriceEdit(name, description, category, student1, student2, fcltyStfAlum1, fcltyStfAlum2, pub1, pub2)
{
   console.log("Executing Price Edit");
   console.log(name + "-" + description + ", " + category + ", " + student1 + ", " + student2 + ", " + fcltyStfAlum1 + ", " + fcltyStfAlum2 + ", " + pub1 + ", " + pub2);

       $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=editPrice&category='+category+'&student1='+student1+"&student2="+student2+"&fcltyStfAlum1="+fcltyStfAlum1+"&fcltyStfAlum2="+fcltyStfAlum2+"&public1="+pub1+"&public2="+pub2,
            success: function(data)
            {
                console.log(data);
                if(data[0] == "Add Price Successful")
                {
                    alert("Prices for '"+name+" - "+description+"' successfully added!");
                    $("#editCategoryPriceModal").modal("toggle");
                    //Refresh(category);
                }
                else if(data[0] == "Edit Price Failed")
                {
                    if(data[1] == "Unexpected Error") alert("An unexpected error occurred!");
                    else if(data[1] == "Values Already Exist") alert("Prices for '"+name+" - "+description+"' not changed! These values already exist!");
                }
                else if(data[0] == "Update Price Successful")
                {
                    alert("Price updates for '"+name+" - "+description+"' successful!");
                    $("#editCategoryPriceModal").modal("toggle");
                    //Refresh(category);                    
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        });
}

function DeleteCategory(name, categoryID, parentID)
{
    console.log("Deleting category '"+name+"' w/ Category #"+categoryID+" and Parent #"+parentID);
    document.getElementById("showCurrentCategoryForDelete").innerHTML = "Deleting Category: " + name;
    document.getElementById("deleteCategoryConfirmation").innerHTML = "Are you sure you wish to proceed?";
    document.getElementById("hiddenCategoryNameForDelete").value = name;
    document.getElementById("hiddenCategoryForDelete").value = categoryID;
    document.getElementById("hiddenParentForDelete").value = parentID;
    $("#deleteCategoryModal").modal();
}
function ExecuteCategoryDelete(name, categoryID, parentID)
{
    console.log("Confirm: Deleting category '"+name+"' w/ Category #"+categoryID+" and Parent #"+parentID);
    $.ajax({
        type: "GET",
        url: '../include/updateInventory.php?action=deleteCategory&name='+name+'&category='+categoryID+'&parent='+parentID,
        success: function(data)
        {
            console.log(data);
            if(data[0] == "Delete Successful")
            {
                alert("Category '"+name+"' successfully deleted!");
                $("#deleteCategoryModal").modal("toggle");
                Refresh(parentID);
            }
            else if(data[0] == "Delete Failed")
            {
                alert("An unexpected error occurred when attempting to delete category '"+name+"'");
            }
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });
}

// INVENTORY
function AddNewInventoryItem(description, productID)
{
    alert(productID);
    console.log("Add New Inventory Item");
    console.log(productID);
}

function DeleteInventoryItem(name, description, category)
{
    console.log("Deleting: "+name+": "+description+" with category "+category);
    document.getElementById("showInventoryItemForDelete").innerHTML = "Remove ALL inventory items '"+name+": "+description+"' from inventory?";
    document.getElementById("deleteFromInventoryConfirmationMessage").innerHTML = "You can not undo this operation.";
    document.getElementById("hiddenInventoryNameForDelete").value = name;
    document.getElementById("hiddenInventoryDescriptionForDelete").value = description;
    document.getElementById("hiddenInventoryCategoryForDelete").value = category;
    $("#deleteInventoryItemModal").modal();
}

function ExecuteInventoryItemDelete(name, description, category)
{
    console.log("Executing Delete For: '"+name+": "+description+"' at category "+category);
    $.ajax({
        type: "GET",
        url: '../include/updateInventory.php?action=deleteFromInventory&name='+name+'&description='+description+'&category='+category,
        success: function(data)
        {
            if(data[0] == "Archived Successful")
            {
                console.log(data);
                alert("Done!");
                $("#deleteInventoryItemModal").modal("toggle");
                Refresh(data[1]);
            }
            else if(data[0] == "Archived Failed")
            {
                alert("An unexpected error occurred when attempting to delete inventory item '"+name+": "+description+"'");
                $("#deleteInventoryItemModal").modal("toggle");
            }
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });
}




function IncrementInventoryItem(category)
{
    console.log("Incrementing inventory with category: " + category);
    document.getElementById("showInventoryItemForIncrement").innerHTML = "You are about to increment the quantity of this inventory item.";

    document.getElementById("hiddenCategoryForInventoryItemIncrementation").value = category;
    document.getElementById("submitIncrementInventoryItemButton").disabled = true;
    document.getElementById("returnFromRepair").checked = false;
    document.getElementById("incrementNew").checked = false;

    $('#incrementPurchaseDateLabel').hide();
    $('#incrementPurchaseCostLabel').hide(); 
    $('#inventoryItemPurchaseDate').hide();
    $('#inventoryItemCost').hide();

    $("#incrementInventoryItemModal").modal();
}

function ExecuteInventoryItemIncrementation(category, date, cost)
{
    var proceed = false;
    var dateGood = false;
    var costGood = false;

    if(document.getElementById("returnFromRepair").checked)
    {
        console.log("Executing Return From Repair - Category: " + category);
        $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=incrementFromRepair&category='+category,
            success: function(data)
            {
                if(data[0] == "Return From Repair Successful")
                {
                    console.log(data);
                    alert(data[1][0]['pName'] + ": " + data[1][0]['pDescription'] + " incremented by 1");
                }
                else if(data[0] == "Return From Repair Failed")
                {
                    alert("An unexpected error occurred.  Are you sure an item was marked for repair?");
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        }); 
    }


    else if(document.getElementById("incrementNew").checked)
    {
        if(date == "")
        { 
            alert("Date invald!");
            dateGood = false;
        }
        else
        {
            for(var i = 0; i < date.length; i++)
            {
                if(date[i] == "'" || date[i] == "`")
                {
                    alert("Date contains invalid characters!");
                    dateGood = false;
                    i = date.length;
                }
                else dateGood = true;
            }
        }


        if(cost == "")
        {    
            alert("Cost can not be empty!");
            costGood = false;
        }
        else
        {
            for(var i = 0; i < cost.length; i++)
            {
                if(cost[i] == "'" || cost[i] == "`")
                {
                    alert("Cost contains invalid characters!");
                    costGood = false;
                    i = cost.length;
                }
                else costGood = true;
            }
        }

        if(dateGood && costGood) 
        {
            console.log("Executing - Incrementing with values: "+category+", "+date+", "+cost);
            $.ajax({
                type: "GET",
                url: '../include/updateInventory.php?action=incrementNew&category='+category+'&date='+date+'&cost='+cost,
                success: function(data)
                {
                    if(data[0] == "Incrementation Successful")
                    {
                        console.log(data);
                        alert(data[1][0]['pName'] + ": " + data[1][0]['pDescription'] + " incremented by 1");
                    }
                    else if(data[0] == "Incrementation Failed")
                    {
                        alert("An unexpected error occurred when attempting to increment quantity of inventory item");
                    }
                }, 
                failure: function(data)
                {
                    console.log("Error: " + data);
                },
                dataType: "json"
            }); 
        }
    }
}



function ToggleCheckboxes(action)
{
    console.log(action);
    var fieldsToggled = false;


    document.getElementById("submitIncrementInventoryItemButton").disabled = false;
    document.getElementById("submitDecrementInventoryItemButton").disabled = false;

    if(action == "repair")
    {
        if(document.getElementById("flagForRepair").checked) document.getElementById("flagForRemoval").checked = false;
        else document.getElementById("submitDecrementInventoryItemButton").disabled = true;
    }
    else if(action == "remove")
    {
        if(document.getElementById("flagForRemoval").checked) document.getElementById("flagForRepair").checked = false;
        else document.getElementById("submitDecrementInventoryItemButton").disabled = true;
    }
    else if(action == "returnFromRepair")
    {
        if(document.getElementById("returnFromRepair").checked)
        {
            document.getElementById("incrementNew").checked = false;
            if($("#inventoryItemPurchaseDate").is(":visible"))
            {
                jQuery('#incrementPurchaseDateLabel').toggle('show'); 
                jQuery('#incrementPurchaseCostLabel').toggle('show');                              
                jQuery('#inventoryItemPurchaseDate').toggle('show');
                jQuery('#inventoryItemCost').toggle('show');
            }
        }
        else document.getElementById("submitIncrementInventoryItemButton").disabled = true;
    }
    else if(action == "incrementNew")
    {
        if(document.getElementById("incrementNew").checked)
        {
            document.getElementById("returnFromRepair").checked = false;
            jQuery('#incrementPurchaseDateLabel').toggle('show');
            jQuery('#incrementPurchaseCostLabel').toggle('show'); 
            jQuery('#inventoryItemPurchaseDate').toggle('show');
            jQuery('#inventoryItemCost').toggle('show');
        }   
        else
        {
            document.getElementById("submitIncrementInventoryItemButton").disabled = true;
            jQuery('#incrementPurchaseDateLabel').toggle('show'); 
            jQuery('#incrementPurchaseCostLabel').toggle('show');                       
            jQuery('#inventoryItemPurchaseDate').toggle('show');
            jQuery('#inventoryItemCost').toggle('show');
        }
    }
}

function DecrementInventoryItem(category)
{
    console.log("Decrementing: " + category);
    document.getElementById("submitDecrementInventoryItemButton").disabled = true;
    document.getElementById("flagForRepair").disabled = false;
    document.getElementById("flagForRepair").checked = false;
    document.getElementById("flagForRemoval").disabled = false;
    document.getElementById("flagForRemoval").checked = false;

    document.getElementById("showInventoryItemForDecrement").innerHTML = "You are about to flag a single item for removal!";
    document.getElementById("hiddenCategoryForInventoryItemDecrementation").value = category;
    $("#decrementInventoryItemModal").modal();
}

function ExecuteInventoryItemDecrementation(category, repair, remove)
{
    var action;
    document.getElementById("flagForRepair").disabled = true;
    document.getElementById("flagForRemoval").disabled = true;
    document.getElementById("submitDecrementInventoryItemButton").disabled = true;

    if(document.getElementById("flagForRepair").checked) action = repair;
    else if(document.getElementById("flagForRemoval").checked) action = remove;
    console.log("Decrementing inventory with category: " + category + ".  Performing: " + action);
    
    $.ajax({
        type: "GET",
        url: '../include/updateInventory.php?action='+action+'&category='+category,
        success: function(data)
        {
            if(data[0] == "Decrementation Successful")
            {
                console.log(data);
                if(data[1] == "repair")
                {
                    alert(data[2][0]['pName'] + ": " + data[2][0]['pDescription'] + " has been marked for repair and has now been hidden from inventory");
                }
                else if(data[1] == "remove")
                {
                    alert(data[2][0]['pName'] + ": " + data[2][0]['pDescription'] + " has now been hidden from inventory");
                }
                $("#decrementInventoryItemModal").modal("toggle");
                document.getElementById("submitDecrementInventoryItemButton").disabled = false;
            }
            else if(data[0] == "Decrementation Failed")
            {
                alert("Only 1 " + data[1][0]['pName'] + ": " + data[1][0]['pDescription'] + " remains.");
            }
        }, 
        failure: function(data)
        {
            console.log("Error: " + data);
        },
        dataType: "json"
    });  
}

function AddInventory(category)
{
    console.log("Adding inventory to category: " + category);
    document.getElementById("hiddenCategoryForAddingInventory").value = category;
    document.getElementById("submitNewInventoryItemButton").disabled = false;
    $("#addInventoryItemModal").modal();
}

function VerifyAddInventory(category, name, description, lifespan, date, cost, reservable)
{
    var nameGood = false;
    var descriptionGood = false;
    var lifespanGood = false;
    var dateGood = false;
    var costGood = false;

    if(name == "") alert("Name can not be empty!");
    else
    {
        for(var i = 0; i < name.length; i++)
        {
            if(name[i] == "'" || name[i] == "`")
            {
                alert("Name contains invalid characters!");
                nameGood = false;
                i = name.length;
            }
            else nameGood = true;
        }
    }
    if(description == "") alert("Description can not be empty!");
    else
    {
        for(var i = 0; i < description.length; i++)
        {
            if(description[i] == "'" || description[i] == "`")
            {
                alert("Description contains invalid characters!");
                descriptionGood = false;
                i = description.length;
            }
            else descriptionGood = true;
        }
    }
    if(lifespan == "") alert("Lifespan can not be empty!");
    else
    {
        for(var i = 0; i < lifespan.length; i++)
        {
            if(lifespan[i] == "'" || lifespan[i] == "`")
            {
                alert("Lifespan contains invalid characters!");
                lifespanGood = false;
                i = lifespan.length;
            }
            else lifespanGood = true;
        }
    }
    if(date == "")
    { 
        alert("Date invald!");
        dateGood = false;
    }
    else
    {
        for(var i = 0; i < date.length; i++)
        {
            if(date[i] == "'" || date[i] == "`")
            {
                alert("Date contains invalid characters!");
                dateGood = false;
                i = date.length;
            }
            else dateGood = true;
        }
    }
    if(cost == "")
    {    
        alert("Cost can not be empty!");
        costGood = false;
    }
    else
    {
        for(var i = 0; i < cost.length; i++)
        {
            if(cost[i] == "'" || cost[i] == "`")
            {
                alert("Cost contains invalid characters!");
                costGood = false;
                i = cost.length;
            }
            else costGood = true;
        }
    }
    if(document.getElementById("newItemReservable").checked)
    {
        var reservable = 0;
        alert(name + ": " + description + " will not be available for rental!");
    }
    else var reservable = 1;

    if(nameGood && descriptionGood && lifespanGood && dateGood && costGood)
    {
        console.log("Executing Inventory Addition - Values: " + name + ", " + description + ", " + lifespan + ", " + date + ", " + cost + ", " + reservable);
        document.getElementById("submitNewInventoryItemButton").disabled = true;
        $.ajax({
            type: "GET",
            url: '../include/updateInventory.php?action=addNewInventoryItem&category='+category+'&name='+name+'&description='+description+
            '&lifespan='+lifespan+'&date='+date+'&cost='+cost+'&reservable='+reservable,
            success: function(data)
            {
                if(data[0] == "Inventory Addition Successful")
                {
                    console.log(data);
                    alert("'"+data[1][0]['pName'] + ": " + data[1][0]['pDescription'] + "'' has been added to inventory!");
                    $("#addInventoryItemModal").modal("toggle");
                    document.getElementById("submitNewInventoryItemButton").disabled = false;
                    Refresh(category);
                }
                else if(data[0] == "Inventory Addition Failed")
                {
                    alert("An unexpected error occurred. '"+ data[1][0]['pName'] + ": " + data[1][0]['pDescription'] + "'' has NOT been added to inventory!");
                }
            }, 
            failure: function(data)
            {
                console.log("Error: " + data);
            },
            dataType: "json"
        });  
    }
}

function EditPackages(category, description)
{
    console.log("Load Packages: " + category);
    document.getElementById("packageList").innerHTML = "";
    document.getElementById("addToPackageHeader").innerHTML = "Add '"+description+"' to a package";
    $.ajax({
        type: "GET",
        url: '../include/getPackages.php?action=getPackages',
        success: function (data) {
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    document.getElementById("packageList").innerHTML += '<li>' + data[i]["packageID"]+ ': ' + data[i]["packageName"] +'  <a class="btn btn-info btn-sm" onclick="SelectPackage('+category+', '+data[i]["packageID"]+ ")\"" +'><i class="zmdi zmdi-plus-circle"></i></a></li>';
                }
            }
        },
        failure: function() {
            document.getElementById("packageList").innerHTML = "";
        },
        dataType: "json"
    });
    $("#packageModal").modal();
}

function SelectPackage(category, packageID)
{
    console.log("Select Package: " + category + ", " + packageID);
    $.ajax({
        type: "GET",
        url: '../include/updateInventory.php?action=addToPackageItems&category='+category+'&packageID='+packageID,
        success: function (data) 
        {
            if(data[0] == "Add To Package Items Successful")
            {
                alert(category+" successfully added to package "+packageID);
                $("#packageModal").modal("toggle");
            }
            else if(data[0] == "Add To Package Items Failed")
            {
                alert("Attempt to add items to package items failed");
            }
        },
        failure: function() 
        {
            alert("An unexpected error occurred.");
        },
        dataType: "json"
    });
}

function SingleDatePicker(category, description)
{
    console.log("Single Date Picker Selection: " + category);
    document.getElementById("singleDatePickerField").value = "";
    document.getElementById("hiddenCategoryForSingleDatePicker").value = category;
    document.getElementById("hiddenDescriptionForSingleDatePicker").value = description;
    document.getElementById("singleDatePickerQuantity").innerHTML = "";

    $("#singleDatePickerModal").modal();
}

function GatherSingleDatePickerData()
{
    var date = document.getElementById("singleDatePickerField").value;
    var category = document.getElementById("hiddenCategoryForSingleDatePicker").value;
    var description = document.getElementById("hiddenDescriptionForSingleDatePicker").value;
    document.getElementById("singleDatePickerQuantity").innerHTML = "Gathering quantity for '"+description+"'...";

    console.log("Gathering data for: " + date + " with category " + category);

    $.ajax({
        type: "GET",
        url: '../include/DatePicker.php?action=singleDatePicker&category='+category+'&date='+date,
        success: function (data) 
        {
            console.log(data);
            var qty = data[0];
            var day = date.substring(8, date.length);
            var month = date.substring(5, 7);
            var year = date.substring(0, 4);
            document.getElementById("singleDatePickerQuantity").innerHTML = "There are "+qty+" '"+description+"' available on "+month+"-"+day+"-"+year;// month+"-"+day+"-"+year;
        },
        failure: function() 
        {
            alert("An unexpected error occurred.");
        },
        dataType: "json"
    });
}


function CloseSingleDatePicker()
{
    $("#singleDatePickerModal").modal("toggle");
}

function ShowPrices(description, category)
{
    console.log("Showing prices for: " + description + ", category: " + category);
    document.getElementById("showCategoryForPrices").innerHTML = "'"+description+"'";
    document.getElementById("hiddenCategoryForShowingPrices").value = category;
    $("#showInventoryPricesModal").modal();

    $.ajax({
        type: "GET",
        url: '../include/displayPrices.php?action=displayPrices&category='+category,
        success: function (data) 
        {
            console.log(data);
            if(data[0] == "Show Prices")
            {
                document.getElementById("showStudentOneDayPrice").innerHTML = "<center>$"+data[1][0]['student']+"</center>";
                document.getElementById("showStudentTwoPlusDaysPrice").innerHTML = "<center>$"+data[1][0]['studentPlus']+"</center>";
                document.getElementById("showFSAOneDayPrice").innerHTML = "<center>$"+data[1][0]['facultyStaffAlumn']+"</center>";
                document.getElementById("showFSATwoPlusDaysPrice").innerHTML = "<center>$"+data[1][0]['facultyStaffAlumnPlus']+"</center>";
                document.getElementById("showPublicOneDayPrice").innerHTML = "<center>$"+data[1][0]['public']+"</center>";
                document.getElementById("showPublicTwoPlusDaysPrice").innerHTML = "<center>$"+data[1][0]['publicPlus']+"</center>";
            }   
            else if(data[0] == "No Prices")
            {
                document.getElementById("displayErrorForShowPrices").innerHTML = "No prices exist for this category...";
            }         
        },
        failure: function() 
        {
            alert("An unexpected error occurred.");
        },
        dataType: "json"
    });
}
function CloseShowPrices()
{
    $("#showInventoryPricesModal").modal("toggle");
}
