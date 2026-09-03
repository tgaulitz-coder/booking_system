<?php

require "../includes/auth.php";

requireLogin();
requireAdmin();


require "../includes/header.php";

?>


<h2>
    Alla bokningar
</h2>


<!-- Fylls i av adminBookings.js med ett kort per bokning, med information om vem som bokad och tid etc.
     Hämtas via ajax/adminGetBookings.php -->
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