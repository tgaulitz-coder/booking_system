<?php

require "includes/auth.php";

requireLogin();


require "includes/header.php";

?>


<h2>
    Boka tid
</h2>


<!-- Används av appointments.js för att visa lyckade/misslyckade meddelanden efter bokningsförsök. -->
<div id="message"></div>


<!-- Om användaren redan har en aktiv bokning visas information om den här istället för listan med lediga tider -->
<div id="currentBooking"></div>



<div id="bookingSection">

    <label>
        Önskemål eller meddelande
    </label>


    <br>


    <textarea
        id="bookingMessage"
        maxlength="500"
        placeholder="Skriv eventuell information till administratören"></textarea>

    <span id="bookingMessageCount" class="charCount">0/500</span>


    <hr>


    <!-- Fylls i av appointments.js med ett kort pet ledig tid, hämtade via ajax/getAppointments.php -->
    <div id="appointments">

        Laddar tider...

    </div>

</div>




<script src="js/utils.js"></script>
<script src="js/appointments.js"></script>


<?php

require "includes/footer.php";

?>