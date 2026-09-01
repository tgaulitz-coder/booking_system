<?php

require "../includes/auth.php";

requireLogin();


if (!isAdmin()) {

    header("Location: ../dashboard.php");

    exit;
}


require "../includes/header.php";

?>


<h2>
    Adminpanel
</h2>


<div class="dashboard-menu">


    <a class="appointment" href="appointments.php">

        <h3>
            Hantera tider
        </h3>

        <p>
            Skapa och ta bort bokningsbara tider.
        </p>
    </a>



    <a class="appointment" href="bookings.php">

        <h3>
            Alla bokningar
        </h3>

        <p>
            Se vilka användare som bokat.
        </p>
    </a>

</div>

<div class="btnGroup">

    <a class="btn btn-secondary" href="../dashboard.php">
        ← Tillbaka till meny
    </a>

</div>

<?php

require "../includes/footer.php";

?>