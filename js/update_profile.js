$("#updateBtn").click(function () {

    const token = localStorage.getItem("session_token");

    const data = {
        dob: $("#dob").val(),
        age: $("#age").val(),
        contact: $("#contact").val()
    };
    console.log("hi");
    console.log({
    dob: $("#dob").val(),
    age: $("#age").val(),
    contact: $("#contact").val()
    });
    $.ajax({
        url: "/PHP/update_profile.php",
        type: "POST",
        contentType: "application/json",
        headers: {
            "Authorization": "Bearer " + token
        },
        data: JSON.stringify(data),
        success: function () {
            alert("Profile updated successfully");
        },
        error: function () {
            alert("Update failed");
        }
    });

});
