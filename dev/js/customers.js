var link;

//Display confirm dialog for deleting a customer
$(document).on("click", "#deleteButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#deleteCustomerModal").modal();
});

//Calls delete using the link they clicked on
function deleteCustomer() {
    document.location.href = link;
}

//Display waiver modal
$(document).on("click", "#waiverSigned", function() {
    $("#customerWaiverModal").modal('togggle');
});

//If they click yes, set the attribute to be sent on form submit
function waiverYes() {
    document.getElementById("customerWaiver").setAttribute("value", "1")
    $("#customerWaiverModal").modal();
}

//Display balance edit modal
$(document).on("click", "#editBalance", function() {
    $("#customerBalanceModal").modal();
});

//Submit the new balance
$(document).on("click", "#editBalanceSubmit", function() {
    document.location.href = "customers.php?action=editBalance&newBalance=" + document.getElementById("newBalanceInput").value + "&customerID=" + document.getElementById("hiddenCustomerID").value;
});
