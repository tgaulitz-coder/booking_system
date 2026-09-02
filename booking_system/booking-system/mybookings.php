<?php

require "includes/auth.php";

requireLogin();

require "includes/header.php";

?>


<h2>
    Mina bokningar
</h2>


<div id="message"></div>


<div id="bookings">

    Laddar bokningar...

</div>



<div class="btnGroup">

    <a class="btn btn-secondary" href="dashboard.php">
        ← Tillbaka till meny
    </a>

</div>



<script src="js/utils.js"></script>
<script src="js/mybookings.js?v=2"></script>


<?php

require "includes/footer.php";

?>