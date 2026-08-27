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
    Alla bokningar
</h2>



<div id="bookings">

    Laddar bokningar...

</div>



<p>

    <a href="index.php">
        ← Tillbaka till adminpanelen
    </a>

</p>


<p>

    <a href="../dashboard.php">
        ← Tillbaka till dashboard
    </a>

</p>



<script src="../js/adminBookings.js"></script>



<?php

require "../includes/footer.php";

?>