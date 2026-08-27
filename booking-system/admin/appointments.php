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
    Hantera tider
</h2>


<div id="message"></div>



<form id="appointmentForm">


    <label>
        Datum
    </label>

    <br>

    <input
        type="date"
        id="date"
        required>


    <br><br>


    <label>
        Tid
    </label>

    <br>

    <input
        type="time"
        id="time"
        required>


    <br><br>


    <label>
        Information till användaren
    </label>

    <br>

    <textarea
        id="information"
        placeholder="Extra information om tiden"></textarea>


    <br><br>


    <button>
        Skapa tid
    </button>


</form>



<hr>


<div id="appointments">

    Laddar tider...

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



<script src="../js/adminAppointments.js"></script>


<?php

require "../includes/footer.php";

?>