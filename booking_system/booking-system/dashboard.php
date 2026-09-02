<?php

require "includes/auth.php";

requireLogin();

require "includes/header.php";

?>


<h2>
    Meny
</h2>


<p>
    Välkommen
    <strong>
        <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
    </strong>
</p>



<div id="bookingStatus">
    Laddar bokningsstatus...
</div>


<?php if (isAdmin()): ?>

    <div id="adminStats"></div>

<?php endif; ?>



<div class="dashboard-menu">


    <a class="appointment" href="appointments.php">

        <h3>
            Boka tid
        </h3>

        <p>
            Se lediga tider och boka en tid.
        </p>

        <span class="cardLink">
            Gå till bokning →
        </span>
    </a>



    <a class="appointment" href="mybookings.php">

        <h3>
            Mina bokningar
        </h3>

        <p>
            Se eller avboka din nuvarande bokning.
        </p>

        <span class="cardLink">
            Visa bokningar →
        </span>
    </a>



    <?php if (isAdmin()): ?>


        <a class="appointment" href="admin/index.php">

            <h3>
                Adminpanel
            </h3>

            <p>
                Hantera tider och bokningar.
            </p>

            <span class="cardLink">
                Öppna adminpanel →
            </span>
        </a>


    <?php endif; ?>


</div>



<script src="js/utils.js"></script>
<script src="js/dashboard.js"></script>


<?php

require "includes/footer.php";

?>