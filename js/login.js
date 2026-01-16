$(document).ready(function () {

    $("#loginBtn").click(function (e) {
        e.preventDefault(); // 🚨 prevent form reload

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();

        // ✅ REQUIRED FIELD VALIDATION (YOUR REQUEST)
        if (!email || !password) {
            $("#errorMsg").text("All fields required");
            return;
        }

        $.ajax({
            url: "/PHP/login.php",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                email: email,
                password: password
            }),

            success: function (response) {
                console.log("LOGIN RESPONSE:", response);

                if (response.status === "success") {
                    localStorage.setItem("session_token", response.token);
                    window.location.href = "profile.html"; // ✅ redirect
                } else {
                    $("#errorMsg").text("Invalid login");
                }
            },

            error: function (xhr) {
                console.error("LOGIN ERROR:", xhr.responseText);
                $("#errorMsg").text("Server error. Try again.");
            }
        });
    });

});

