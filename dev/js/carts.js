var link;
var productID, cartID;
var startDateOK = false;
var endDateOK = false;

/*
 * Utility checkdate function for start > end check
 * Note that some namespace collisions arise with so many start
 * and end dates floating around. I tried to adhere to good naming
 * conventions to (hopefully) make it clear.
 */
function checkDate() {
    startDateOK = false;
    endDateOK = false;
    if(document.getElementById("cartStartDate").value != null) {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0
        var yyyy = today.getFullYear();
        if(dd<10){
            dd='0'+dd
        }
        if(mm<10){
            mm='0'+mm
        }
        var today = yyyy+'-'+mm+'-'+dd;

        var startDate = new Date(document.getElementById("cartStartDate").value);
        var sdd = startDate.getDate()+1;
        var smm = startDate.getMonth()+1; //January is 0
        var syyyy = startDate.getFullYear();
        if(sdd<10){
            sdd = '0'+sdd
        }
        if(smm<10){
            smm = '0'+smm
        }
        var inputDate = syyyy+'-'+smm+'-'+sdd;
        if(inputDate < today) {
            document.getElementById("startDateInvalidMessage").innerHTML = "Date can't be before today.";
        }
        else {
            document.getElementById("startDateInvalidMessage").innerHTML = "";
            startDateOK = true;
        }
    }
    
    if (document.getElementById("cartEndDate").value != null && document.getElementById("cartStartDate").value != null) {
        var startDate = document.getElementById("cartStartDate").value;
        var endDate = document.getElementById("cartEndDate").value;
        if (endDate < startDate) {
            document.getElementById("endDateInvalidMessage").innerHTML = "End date must be after start date.";
        }
        else {
            document.getElementById("endDateInvalidMessage").innerHTML = "";
            endDateOK = true;
        }
    }

    if(startDateOK && endDateOK) {
        document.getElementById("createButton").disabled = false;
    }
    else {
        document.getElementById("createButton").disabled = true;
    }
}

//Check date function for the edit cart form
function editCheckDate() {
    startDateOK = false;
    endDateOK = false;
    if(document.getElementById("editStartDate").value != null) {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth()+1; //January is 0
        var yyyy = today.getFullYear();
        if(dd<10){
            dd='0'+dd
        }
        if(mm<10){
            mm='0'+mm
        }
        var today = yyyy+'-'+mm+'-'+dd;
        
        var startDate = new Date(document.getElementById("editStartDate").value);
        var sdd = startDate.getDate()+1;
        var smm = startDate.getMonth()+1; //January is 0
        var syyyy = startDate.getFullYear();
        if(sdd<10){
            sdd = '0'+sdd
        }
        if(smm<10){
            smm = '0'+smm
        }
        var inputDate = syyyy+'-'+smm+'-'+sdd;

        if(inputDate < today) {
            document.getElementById("startDateErrorMessage").innerHTML = "Date can't be before today.";
        }
        else {
            document.getElementById("startDateErrorMessage").innerHTML = "";
            startDateOK = true;
        }
    }
    
    if (document.getElementById("editEndDate").value != null && document.getElementById("editStartDate").value != null) {
        var startDate = document.getElementById("editStartDate").value;
        var endDate = document.getElementById("editEndDate").value;
        if (endDate < startDate) {
            document.getElementById("endDateErrorMessage").innerHTML = "End date must be after start date.";
        }
        else {
            document.getElementById("endDateErrorMessage").innerHTML = "";
            endDateOK = true;
        }
    }
    
    if(startDateOK && endDateOK) {
        document.getElementById("saveButton").disabled = false;
    }
    else {
        document.getElementById("saveButton").disabled = true;
    }
}

//Confirm dialog modal for delete cart
$(document).on("click", "#deleteButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#deleteCartModal").modal();
});

//Called if the user clicks the confirm delete button
function deleteCart() {
    document.location.href = link;
}

//Confirm dialog modal for delete cart items
$(document).on("click", "a.deleteCartItem", function(e) {
    e.preventDefault();
    productID = this.getAttribute("data-productID");
    cartID = this.getAttribute("data-cartID");
    $("#deleteCartItemModal").modal();
});

/*
 * This is called if the user confirms they want to delete a cart item
 * Apologies for the different methods of calling functions (jquery vs. onclick attribute)
 * hopefully naming conventions will help
 */
function deleteCartItem() {
    $.ajax({
        type: "GET",
        url: '../include/deleteCartItem.php?cartID='+cartID+'&productID='+productID,
        success: function (data) {
            //Deleted successfully
            getCartItems(cartID);
        },
        failure: function (data) {
            //Delete failed.
            alert("Delete failed.");
        }
    });
}

/*
 * This submits a package to a cart from the package modal
 */
function finishPackage() {
    if(!document.getElementById("numberOfPackages").value) {
        document.getElementById("enterNumberMessage").innerHTML = "Please enter the number of packages."
    }
    else {
        document.getElementById("enterNumberMessage").innerHTML = ""
        var multiple = document.getElementById("numberOfPackages").value;
        var cartID = document.getElementById("hiddenCartID").value;
        var packageID = document.getElementById("hiddenPackageID").value;
        var customerID = document.getElementById("hiddenCustomerID"+cartID).value;
        $.ajax({
            type: "GET",
            url: '../include/getPackageItems.php?action=setTotal&cartID='+cartID+'&packageID='+packageID+'&customerID='+customerID+'&multiple='+multiple,
            success: function (data) {
                console.log(data);
                //document.location.href = "carts.php?cartID=" + data[0]['cartID'];
            },
            failure: function(data) {
                console.log("failure");
            }
        });
        document.location.href="carts.php?cartID="+cartID;
    }
}

/*
 * This function adds a package item to a cart using AJAX
 * Note this is different than the "add to cart" from the inventory page
 */
function addItemToCart(cartID, productID) {
    document.getElementById("message"+productID).innerHTML = "Adding...";
    $.ajax({
        type: "GET",
        url: '../include/getPackageItems.php?action=addToCart&cartID='+cartID+'&productID='+productID,
        success: function (data) {
            document.getElementById("message"+productID).innerHTML = "Added to cart";
        },
        failure: function(data) {
            document.getElementById("message"+productID).innerHTML = "Add to cart failed";
        }
    });
}

/*
 * This function is called from the package listing in the package modal
 * Sets some variables for use later in finishing a package
 */
function selectPackage(cartID, packageID) {
    document.getElementById("hiddenCartID").value = cartID;
    document.getElementById("hiddenPackageID").value = packageID;
    document.getElementById("itemList").innerHTML = "";
    $.ajax({
        type: "GET",
        url: '../include/getPackageItems.php?action=getItems&packageID='+packageID+'&cartID='+cartID,
        success: function (data) {
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    document.getElementById("itemList").innerHTML += '<li>' + data[i]["pDescription"] + '  <a class="btn btn-info btn-sm" title="Add to cart" onclick="addItemToCart(' + cartID + "," + data[i]["pID"] + ')"><i class="zmdi zmdi-plus-circle"></i></a><p id="message'+data[i]["pID"]+'"></p></li>';
                }
            }
        },
        failure: function(data) {
            console.log("failure");
            //document.getElementById("itemList").innerHTML = "";
        },
        dataType: "json"
    });
    $("#packageModal").modal("toggle");
    $("#selectItemsModal").modal();
}

//Displays a modal for the customer to select what package they want
function loadPackages(cartID) {
    document.getElementById("packageList").innerHTML = "";
    $.ajax({
        type: "GET",
        url: '../include/getPackages.php?action=getPackages',
        success: function (data) {
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    document.getElementById("packageList").innerHTML += '<li>' + data[i]["packageID"]+ ': ' + data[i]["packageName"] +'  <a class="btn btn-info btn-sm" onclick="selectPackage('+cartID+', '+data[i]["packageID"]+ ")\"" +'><i class="zmdi zmdi-plus-circle"></i></a></li>';
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

//Calls a file in include directory to list cart items
function getCartItems(cartID) {
    $.ajax({
        type: "GET",
        url: '../include/getCartItems.php?cartID='+cartID,
        success: function (data) {
            if(data.length == 0) {
                document.getElementById("cartItemsList" + cartID).innerHTML = '<li class="list-group-item">This cart is empty.</li>';
            }
            else {
                document.getElementById("cartItemsList" + cartID).innerHTML = '<li class="list-group-item"><b>Items:</b></li>';;
                for (var i = 0; i < data.length; i++) {
                    deleteString = "<a class='deleteCartItem' data-productID=" + data[i]['pID'] + " data-cartID=" + data[i]['cartID'] + " title='Delete from cart'><i class='zmdi zmdi-delete'></i><a>";
                    document.getElementById("cartItemsList" + cartID).innerHTML += '<li class="list-group-item">' + data[i]["pDescription"] + " " + deleteString + '</li>';
                }
            }
        },
        dataType: "json"
    });
}

//Extra check for negative numbers
function noNegatives() {
    if(document.getElementById("numberOfPackages").value < 1) {
        document.getElementById("numberOfPackages").value = 1;
    }
    
}