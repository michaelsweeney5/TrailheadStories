var globalCategoryID;
var globalPackageID;

//Confirm dialog modal for delete package items
$(document).on("click", "a.deletePackageItem", function(e) {
    e.preventDefault();
    globalCategoryID = this.getAttribute("data-categoryID");
    globalPackageID = this.getAttribute("data-packageID");
    $("#deletePackageItemModal").modal();
});

//AJAX call to the include directory to delete an item from a package
function deletePackageItem() {
    $.ajax({
    
        type: "GET",
        url: '../include/getPackageItems.php?action=deletePackageItem&packageID='+globalPackageID+'&categoryID='+globalCategoryID,
        success: function (data) {
            getPackageItems(globalPackageID);
        },
        failure: function(data) {
            alert("Delete Failed.");
        },
        dataType: "json"
    });
}

//Listing function for packages
function getPackageItems(packageID) {
    document.getElementById("packageItemsList" + packageID).innerHTML = "";
    $.ajax({
        type: "GET",
        url: '../include/getPackageItems.php?action=getOnePackageItems&packageID='+packageID,
        success: function (data) {
            if (data.length > 0) {
                for (var i = 0; i < data.length; i++) {
                    deleteString = "<a class='deletePackageItem' data-packageID=" + data[i]['packageID'] + " data-categoryID=" + data[i]['pCategory'] + " title='Delete from package'><i class='zmdi zmdi-delete'></i><a>";
                    document.getElementById("packageItemsList" + packageID).innerHTML += '<li class="list-group-item">' + data[i]['pName']+ ", " + data[i]["pDescription"] + " " + deleteString + '</li>';
                }
            }
        },
        failure: function(data) {
            console.log("failure");
        },
        dataType: "json"
    });
}
