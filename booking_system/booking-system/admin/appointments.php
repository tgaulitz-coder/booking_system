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



<div class="formTabs">

    <button type="button" class="formTabButton active" data-target="singleFormWrapper">
        En tid
    </button>

    <button type="button" class="formTabButton" data-target="bulkFormWrapper">
        Flera tider
    </button>

</div>



<div id="singleFormWrapper">

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
            maxlength="500"
            placeholder="Extra information om tiden"></textarea>

        <span id="informationCount" class="charCount">0/500</span>


        <br><br>


        <button>
            Skapa tid
        </button>


    </form>

</div>



<div id="bulkFormWrapper" style="display: none;">

    <p class="helpText">
        Skapar flera lediga tider automatiskt utifrån ett datumspann, en start-/sluttid och ett intervall.
        Exempel: 2026-09-01 till 2026-09-05, kl. 09:00–12:00, var 30:e minut ger 6 tider per dag.
    </p>

    <form id="bulkAppointmentForm">

        <label>Startdatum</label>
        <br>
        <input type="date" id="bulkStartDate" required>

        <br><br>

        <label>Slutdatum</label>
        <br>
        <input type="date" id="bulkEndDate" required>

        <br><br>

        <label>Starttid</label>
        <br>
        <input type="time" id="bulkStartTime" required>

        <br><br>

        <label>Sluttid</label>
        <br>
        <input type="time" id="bulkEndTime" required>

        <br><br>

        <label>Intervall</label>
        <br>
        <select id="bulkInterval">
            <option value="15">Var 15:e minut</option>
            <option value="30" selected>Var 30:e minut</option>
            <option value="45">Var 45:e minut</option>
            <option value="60">Varje timme</option>
            <option value="90">Var 90:e minut</option>
            <option value="120">Varannan timme</option>
        </select>

        <br><br>

        <label>
            <input type="checkbox" id="bulkSkipWeekends" checked style="width: auto; margin: 0;">
            Hoppa över lördagar och söndagar
        </label>

        <br><br>

        <label>
            <input type="checkbox" id="bulkUseBreak" style="width: auto; margin: 0;">
            Lägg till en paus (t.ex. lunch) som inga tider ska skapas inom
        </label>

        <br><br>

        <div id="bulkBreakFields" style="display: none;">

            <label>Paus från</label>
            <br>
            <input type="time" id="bulkBreakStart">

            <br><br>

            <label>Paus till</label>
            <br>
            <input type="time" id="bulkBreakEnd">

            <br><br>

        </div>

        <label>
            Information till användaren (gäller alla skapade tider)
        </label>

        <br>

        <textarea
            id="bulkInformation"
            maxlength="500"
            placeholder="Valfritt, gäller samtliga skapade tider"></textarea>

        <span id="bulkInformationCount" class="charCount">0/500</span>

        <br><br>

        <button>
            Skapa tider
        </button>

    </form>

</div>



<hr>


<div id="appointments">

    Laddar tider...

</div>



<div class="btnGroup">

    <a class="btn btn-secondary" href="index.php">
        ← Tillbaka till adminpanelen
    </a>

    <a class="btn btn-secondary" href="../dashboard.php">
        ← Tillbaka till dashboard
    </a>

</div>



<script src="../js/utils.js"></script>
<script src="../js/adminAppointments.js"></script>

<?php

require "../includes/footer.php";

?>