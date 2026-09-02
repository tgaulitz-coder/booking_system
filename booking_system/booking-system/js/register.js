document
.getElementById("registerForm")
.addEventListener("submit", function(e){

    e.preventDefault();

    const submitButton = e.target.querySelector("button[type='submit'], button:not([type])");
    if (submitButton) submitButton.disabled = true;

    let data = {

        name:
        document.getElementById("name").value,

        username:
        document.getElementById("username").value,

        email:
        document.getElementById("email").value,

        password:
        document.getElementById("password").value

    };


    fetch("ajax/register.php", {

        method:"POST",

        headers:{
            "Content-Type":"application/json",
            "X-CSRF-Token": getCsrfToken()
        },

        body:
        JSON.stringify(data)

    })


    .then(response => response.json())


    .then(result => {

        document.getElementById("message")
        .textContent = result.message;


        if(result.success){

    document.getElementById("registerForm").reset();

    document.getElementById("message").textContent =
        "Kontot skapades. Du skickas till inloggningen...";

    setTimeout(() => {

        window.location.href = "login.php";

    }, 1500);

    return;

}

    if (submitButton) submitButton.disabled = false;

    })


    .catch(error=>{

        console.log(error);

        if (submitButton) submitButton.disabled = false;

    });


});
