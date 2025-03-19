<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nursing Clinic</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Wellness Clinic</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Dashboard</a></li>
                <li><a href="schedule.php">Schedules</a></li>
                <li><a href="patients.php">Patients</a></li>
                <li><a href="prescriptions.html">Prescriptions</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Welcome to the Wellness Clinic Project</h2>
    </main>
    <aside>
        <?php echo $aside; ?>
    </aside>
    <div class="main">
        <?php echo $main; ?>
    </div>
    <footer>
        <p>&copy; Wellness Clinic Project</p>
    </footer>
</body>

</html>