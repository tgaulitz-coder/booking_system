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


<p>
    Här kan du hantera dina bokningar.
</p>



<div class="dashboard-menu">


    <div class="appointment">

        <h3>
            Boka tid
        </h3>

        <p>
            Se lediga tider och boka en tid.
        </p>

        <a href="appointments.php">
            Gå till bokning
        </a>

    </div>



    <div class="appointment">

        <h3>
            Mina bokningar
        </h3>

        <p>
            Se eller avboka din nuvarande bokning.
        </p>

        <a href="mybookings.php">
            Visa bokningar
        </a>

    </div>



    <?php if (isAdmin()): ?>


        <div class="appointment">

            <h3>
                Adminpanel
            </h3>

            <p>
                Hantera tider och bokningar.
            </p>

            <a href="admin/index.php">
                Öppna adminpanel
            </a>

        </div>


    <?php endif; ?>


</div>



<?php

require "includes/footer.php";

?>