//Confirm dialog modal for delete cart
$(document).on("click", "#deleteButton", function(e) {
    e.preventDefault();
    link = $(this).attr("href");
    $("#deleteUserModal").modal();
});

function deleteCart() {
    document.location.href = link;
}