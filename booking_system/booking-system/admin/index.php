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


    <div class="appointment">

        <h3>
            Hantera tider
        </h3>

        <p>
            Skapa och ta bort bokningsbara tider.
        </p>

        <a href="appointments.php">
            Öppna
        </a>

    </div>



    <div class="appointment">

        <h3>
            Alla bokningar
        </h3>

        <p>
            Se vilka användare som bokat.
        </p>

        <a href="bookings.php">
            Öppna
        </a>

    </div>
</div>


<p>

    <a href="../dashboard.php">
        ← Tillbaka till Meny
    </a>

</p>


</div>



<?php

require "../includes/footer.php";

?>