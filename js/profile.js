$(document).ready(function () {

    const token = localStorage.getItem("session_token");

    if (!token) {
        window.location.href = "login.html";
        return;
    }

    $.ajax({
        url: "/PHP/profile.php",
        type: "GET",
        dataType: "json", // 🔴 IMPORTANT
        headers: {
            "Authorization": "Bearer " + token
        },

        success: function (res) {
            console.log("Profile response:", res);

            if (res.status !== "success") {
                localStorage.removeItem("session_token");
                window.location.href = "login.html";
                return;
            }

            $("#username").val(res.username);
            $("#email").val(res.email);
            $("#dob").val(res.dob);
            $("#age").val(res.age);
            $("#contact").val(res.contact);
        },

        error: function (xhr) {
            console.error("Profile error:", xhr.responseText);
            localStorage.removeItem("session_token");
            window.location.href = "login.html";
        }
    });

    $("#logoutBtn").click(function () {
        localStorage.removeItem("session_token");
        window.location.href = "login.html";
    });
});

