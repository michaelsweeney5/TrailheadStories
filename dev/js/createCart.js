var nameOk = false;
var commentsOk = false;
var customerFirstName="", customerLastName="", customerID="";

//Utility to make sure comments are filled out
function checkComments() {
	if(document.getElementById("cartComments").value != null) {
		commentsOk = true;
	}
}

//Called when user chooses a customer, sets some global variables
function selectCustomer(id,first,last) {
	customerID = id.toString();
	customerFirstName = first;
	customerLastName = last;
	document.getElementById("nameMessage").innerHTML = "Customer: " + customerID + ": " + customerFirstName + " " + customerLastName + " selected."
	nameOk = true;
}

//Calls a file in include directory to serach customers
function findCustomer() {
	var str = document.getElementById("customerSearch").value;
	document.getElementById("searching").innerHTML = "Searching...";
	var responseText = document.getElementById("nameMessage");
	responseText.innerHTML = "";
	if(str == null) {
		console.log("Name is null");
		return;
	}
    $.ajax({
        type: "GET",
        url: '../include/getCustomer.php?search='+str,
        success: function (data) {
			document.getElementById("searching").innerHTML = "";
			if(data.length == 0) {
				responseText.innerHTML = "<p>No customer found, click to add. </p> <a href=\"../customers.php?action=add\" class=\"btn btn-info btn-s\">Add Customer</a>"
			}
			else {
				for (var i = 0; i < data.length; i++) {
					if (data[i]["customerBalance"] != 0) {
						iconClassStr = '<a class="btn btn-sm btn-info"><i class="zmdi zmdi-hc-2x zmdi-minus-circle"></i></a>';
					}
					else {
						iconClassStr = '<a class="btn btn-sm btn-info" title="Select this customer" onclick="selectCustomer(\''+data[i].customerID+'\',\''+data[i].customerFirstName+'\',\''+data[i].customerLastName+'\')"><i class="zmdi zmdi-hc-2x zmdi-plus-circle"></i></a>';
					}
					responseText.innerHTML += "<li id=\"\"+i>Customer: " + data[i]["customerFirstName"] + " " + data[i]["customerLastName"] +
						", Phone: " + data[i]["customerPhone"] + ", Balance: $" + data[i]["customerBalance"] + "    " + iconClassStr + "</li>";
				}
			}
        },
        dataType: "json"
    });
}

//Submits a cart once we have all the necessary data
function createCart() {
	if(nameOk && commentsOk) {
		document.getElementById("creating").innerHTML = "Creating...";
		document.getElementById("inputInvalidMessage").innerHTML = "";
		$.ajax({
			type: "GET",
			url: '../include/addCart.php?customerID='+customerID+'&cartStartDate='+document.getElementById("cartStartDate").value+'&cartEndDate='+document.getElementById("cartEndDate").value+'&cartComments='+document.getElementById("cartComments").value,
			success: function (data) {
				console.log(data);
				document.getElementById("successMessage").innerHTML = '<p>Cart created successfully.</p><a  class="btn btn-info" href="carts.php"><i class="zmdi zmdi-hc-lg zmdi-close-circle-o"></i></a>';
			},
			failure: function (data) {
				console.log("failure");
			},
			dataType: "json"
		});
	}
	else {
		document.getElementById("inputInvalidMessage").innerHTML = "Must enter valid data.";
	}
}
