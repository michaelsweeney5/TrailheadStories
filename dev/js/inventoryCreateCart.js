var nameOk = false;
var commentsOk = false;
var customerFirstName="", customerLastName="", customerID="";

//Utility checkdate function for start > end check
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

function checkComments() {
	if(document.getElementById("cartComments").value != null) {
		commentsOk = true;
	}
}

function selectCustomer(id,first,last) {
	customerID = id.toString();
	customerFirstName = first;
	customerLastName = last;
	document.getElementById("nameMessage").innerHTML = "Customer: " + customerID + ": " + customerFirstName + " " + customerLastName + " selected."
	nameOk = true;
}

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
						iconClassStr = '<a class="btn btn-sm btn-info" onclick="selectCustomer(\''+data[i].customerID+'\',\''+data[i].customerFirstName+'\',\''+data[i].customerLastName+'\')"><i class="zmdi zmdi-hc-2x zmdi-plus-circle"></i></a>';
					}
					responseText.innerHTML += "<li id=\"\"+i>Customer: " + data[i]["customerFirstName"] + " " + data[i]["customerLastName"] +
						", Phone: " + data[i]["customerPhone"] + ", Balance: $" + data[i]["customerBalance"] + "    " + iconClassStr + "</li>";
				}
			}
        },
        dataType: "json"
    });
}

function createCart(category) {
    if(nameOk && commentsOk) {
		document.getElementById("creating").innerHTML = "Creating...";
		document.getElementById("inputInvalidMessage").innerHTML = "";
		$.ajax({
			type: "GET",
			url: '../include/addCart.php?productID='+globalProduct+'&customerID='+customerID+'&cartStartDate='+document.getElementById("cartStartDate").value+'&cartEndDate='+document.getElementById("cartEndDate").value+'&cartComments='+document.getElementById("cartComments").value,
			success: function (data) {
				document.getElementById("creating").innerHTML = "";
				document.getElementById("successMessage").innerHTML = "";
				$("#buildCartModal").modal('toggle');
				console.log(data);
				window.location.href='inventory.php?category='+category+'&action=reserve&id=' +data['cartID']+'&startDate='+data['cartStartDate']+'&endDate='+data['cartEndDate'];
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

function buildCart() {

	bootbox.dialog({
			title: "Build a new reservation cart.",
			message: '<div class="row">' +
			'<div class="col-md-12"> ' +
				'<form class="form-horizontal" onsubmit="return false;"> ' +
					'<div class="form-group"> ' +
						'<label class="col-md-4 control-label" >Customer Name</label> ' +
							'<div class="col-md-4"> ' +
								'<input id="customerSearch" type="text" placeholder="Search by name/phone" onchange="findCustomer()" class="form-control input-md"> ' +
							'</div> <br/><br/>' +
							'<ul id="nameMessage"></ul>' +

			'</div> ' +
					'<div class="form-group"> ' +
						'<label class="col-md-4 control-label" >Start Date</label> ' +
						'<div class="col-md-4"> ' +
							'<input id="cartStartDate" name="cartStartDate" type="date" class="form-control input-md" placeholder="YYYY-MM-DD"  pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" onchange="checkDate()" required> ' +
						'</div> ' +
					'</div> ' +
					'<div class="form-group"> ' +
						'<label class="col-md-4 control-label" >End Date</label> ' +
						'<div class="col-md-4"> ' +
							'<input id="cartEndDate" name="cartEndDate" type="date" class="form-control input-md" placeholder="YYYY-MM-DD"  pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" onchange="checkDate()" required> ' +
						'</div> ' +
						'<p id="endDateInvalidMessage"></p>' +
					'</div> ' +
					'<div class="form-group"> ' +
						'<label class="col-md-4 control-label" >Trip Description</label> ' +
						'<div class="col-md-4"> ' +
							'<input id="cartComments" name="cartComment" type="text" class="form-control input-md" placeholder="Trip Description"  > ' +
						'</div> ' +
					'</div> ' +
				'</form> ' +
			'</div>' +
			'<div id="successMessage">' +
				'<a class="btn btn-lg btn-info" type="submit" id="createButton" onclick="createCart()"><i class="zmdi zmdi-hc-lg zmdi-check-square"></i></a>' +
				'<p id="inputInvalidMessage"></p>' +
			'</div>'
	}

	);

}
