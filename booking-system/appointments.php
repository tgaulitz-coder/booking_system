<?php

require "includes/auth.php";

requireLogin();


require "includes/header.php";

?>


<h2>
    Boka tid
</h2>



<div id="message"></div>



<label>
    Önskemål eller meddelande
</label>


<br>


<textarea
    id="bookingMessage"
    placeholder="Skriv eventuell information till administratören">
</textarea>



<hr>



<div id="appointments">

    Laddar tider...

</div>




<script src="js/appointments.js"></script>



<?php

require "includes/footer.php";

?>