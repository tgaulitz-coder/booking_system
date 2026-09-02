document
.getElementById("loginForm")
.addEventListener("submit", async function(e){

    e.preventDefault();

    const submitButton = e.target.querySelector("button[type='submit'], button:not([type])");
    if (submitButton) submitButton.disabled = true;

    const data = {

        username:
            document.getElementById("username").value,

        password:
            document.getElementById("password").value

    };

    try{

        const response = await fetch("ajax/login.php",{

            method:"POST",

            headers:{
                "Content-Type":"application/json",
                "X-CSRF-Token": getCsrfToken()
            },

            body:JSON.stringify(data)

        });

        const result = await response.json();

        document.getElementById("message").textContent = result.message;

        if(result.success){

            window.location = "dashboard.php";
            return;

        }

    }

    catch(error){

        console.error(error);

        document.getElementById("message").textContent =
            "Något gick fel.";

    }

    if (submitButton) submitButton.disabled = false;

});
