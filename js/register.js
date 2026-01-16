$(document).ready(function () {
    $("#registerBtn").click(function () {

        let username = $("#username").val().trim();
        let email = $("#email").val().trim();
        let password = $("#password").val();
        let dob = $("#dob").val();
        let age = $("#age").val();
        let contact = $("#contact").val();

        if (!username || !email || !password) {
            alert("All fields required");
            return;
        }

        $.ajax({
            url: "/PHP/register.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                username: username,
                email: email,
                password: password,
                dob: dob,
                age: age,
                contact: contact

            }),
            success: function (res) {
                let response = res;

                if (response.status === "success") {
                    alert("Registration successful");
                    window.location.href = "login.html";
                }
                else if (response.status === "emailexists") {
                    alert("Email already registered");
                }
                else if (response.status === "usernameexists") {
                    alert("Username already registered");
                }
                else {
                    alert("Registration failed");
                }

            }
        });

    });

});
