document
.getElementById("loginForm")
.addEventListener("submit", async function(e){

    e.preventDefault();

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
                "Content-Type":"application/json"
            },

            body:JSON.stringify(data)

        });

        const result = await response.json();

        document.getElementById("message").textContent = result.message;

        if(result.success){

            window.location = "dashboard.php";

        }

    }

    catch(error){

        console.error(error);

        document.getElementById("message").textContent =
            "Något gick fel.";

    }

});