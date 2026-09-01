document
.getElementById("registerForm")
.addEventListener("submit", function(e){

    e.preventDefault();


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
            "Content-Type":"application/json"
        },

        body:
        JSON.stringify(data)

    })


    .then(response => response.json())


    .then(result => {

        document.getElementById("message")
        .innerHTML = result.message;


        if(result.success){

    document.getElementById("registerForm").reset();

    document.getElementById("message").textContent =
        "Kontot skapades. Du skickas till inloggningen...";

    setTimeout(() => {

        window.location.href = "login.php";

    }, 1500);

}


    })


    .catch(error=>{

        console.log(error);

    });


});