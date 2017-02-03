var link;
var productID, invoiceID;
var startDateOk, endDateOk;

/*
 * Utility checkdate function for start < end check
 * More information on the namespace collision in the carts.js file
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

//Utility date checking for the edit invoice form
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

//Confirm dialog modal for delete invoice
$(document).on("click", "#deleteButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#deleteInvoiceModal").modal();
});

//Archives the invoice if they confirm
function deleteInvoice() {
    document.location.href = link;
}

//Confirm dialog modal for delete invoice item
$(document).on("click", "a.deleteInvoiceItem", function(e) {
    e.preventDefault();
    productID = this.getAttribute("data-productID");
    invoiceID = this.getAttribute("data-invoiceID");
    $("#deleteInvoiceItemModal").modal();
});

//Confirm dialog modal for check in invoice
$(document).on("click", "#checkInButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#checkInInvoiceModal").modal();
});

//Checks in the invoice if they confirm
function checkInInvoice() {
    document.location.href = link;
}

//Confirm dialog modal for restore invoice
$(document).on("click", "#restoreButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#restoreInvoiceModal").modal();
});

//Restores the invoice if they confirm
function restoreInvoice() {
    document.location.href = link;
}

//Lists the invoice items
function getInvoiceItems(invoiceID) {
    $.ajax({
        type: "GET",
        url: '../include/getInvoiceItems.php?invoiceID='+invoiceID,
        success: function (data) {
            if(data.length == 0) {
                document.getElementById("invoiceItemsList" + invoiceID).innerHTML = '<li class="list-group-item">This invoice is empty.</li>';
            }
            else {
                document.getElementById("invoiceItemsList" + invoiceID).innerHTML = '<li class="list-group-item"><b>Items:</b></li>';
                for (var i = 0; i < data.length; i++) {
                    document.getElementById("invoiceItemsList" + invoiceID).innerHTML += '<li class="list-group-item">' + data[i]["pDescription"] + '</li>';
                }
            }
        },
        dataType: "json"
    });
}

//Lists the invoice items for old invoices
function getOldInvoiceItems(oldInvoiceID) {
    $.ajax({
        type: "GET",
        url: '../include/getOldInvoiceItems.php?oldInvoiceID='+oldInvoiceID,
        success: function (data) {
            if(data.length == 0) {
                document.getElementById("invoiceItemsList" + oldInvoiceID).innerHTML = '<li class="list-group-item">This invoice is empty.</li>';
            }
            else {
                document.getElementById("invoiceItemsList" + oldInvoiceID).innerHTML = '<li class="list-group-item"><b>Items:</b></li>';
                for (var i = 0; i < data.length; i++) {
                    document.getElementById("invoiceItemsList" + oldInvoiceID).innerHTML += '<li class="list-group-item">' + data[i]["pDescription"] + '</li>';
                }
            }
        },
        dataType: "json"
    });
}