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



<div class="btnGroup">

    <a class="btn btn-secondary" href="index.php">
        ← Tillbaka till adminpanelen
    </a>

    <a class="btn btn-secondary" href="../dashboard.php">
        ← Tillbaka till meny
    </a>

</div>



<script src="../js/utils.js"></script>
<script src="../js/adminBookings.js"></script>


<?php

require "../includes/footer.php";

?>