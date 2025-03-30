<?php
// Required to connect to the wellness clinic database
require_once "includes/config.inc.php";
require_once "includes/db-classes.inc.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Clinic</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Wellness Clinic</h1>
        <nav>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/schedule.php">Schedules</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/reports.php">Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/staff.php">Staff</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/patients.php">Patients</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Welcome to the Wellness Clinic Project</h2>
            <p>Navigate through the menu to manage schedules, reports, staff, and patients.</p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>