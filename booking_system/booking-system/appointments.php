<?php

require "includes/auth.php";

requireLogin();


require "includes/header.php";

?>


<h2>
    Boka tid
</h2>



<div id="message"></div>



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



    <div id="appointments">

        Laddar tider...

    </div>

</div>




<script src="js/utils.js"></script>
<script src="js/appointments.js"></script>


<?php

require "includes/footer.php";

?>