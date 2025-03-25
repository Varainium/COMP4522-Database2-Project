<?php
// Required to connect to the wellness clinic database
require_once "includes/config.inc.php";
require_once "includes/db-classes.inc.php";

try {
    // Connect and retrieve data from StaffDB
    $conn = DatabaseHelper::createConnection(DBCONNSTRING);
    $staffGateway = new StaffDB($conn);

    // Markup for aside content
    $allStaff = $staffGateway->getAll();
    $aside = "";
    if ($allStaff) {
        $aside .= "<!-- All staff container -->
            <h2>Select a Staff</h2>
            <form method='GET' action='" . $_SERVER['REQUEST_URI'] . "'>";
        $aside .= "<ul>";
        foreach ($allStaff as $row) {
            $aside .= "<li><button type='submit' name='ref' value='" . htmlspecialchars($row['staff_id']) . "'>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</button></li>";
        }
        $aside .= "</ul>";
        $aside .= "</form>";

        // Markup for main content
        $main = "";
        // Retrieve staff details
        if (isset($_GET['ref']) && !empty($_GET['ref'])) {
            $staff = $staffGateway->getStaff($_GET['ref']);

            // Grab element values and set them in variables
            $staffName = htmlspecialchars($staff['first_name'] . " " . $staff['last_name']);
            $department = htmlspecialchars($staff['department']);
            $phone = htmlspecialchars($staff['phone']);

            // Output the staff information
            $main .=
                "<!-- Staff information -->
                <section class='info'>
                    <h2>$staffName</h2>
                    <div class='grid'>
                        <p><strong>Department: </strong>$department</p>
                        <p><strong>Phone: </strong>$phone</p>
                    </div>
                </section>";
        }
    } else {
        $aside .= "No data to retrieve. Please reconfigure connection to database.";
        $main .= "No data to retrieve. Please reconfigure connection to database.";
    }
} catch (Exception $e) {
    die($e->getMessage());
}

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
                <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/schedule.php">Schedules</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/reports.php">Reports</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/staff.php">Staff</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/patients.php">Patients</a></li>
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