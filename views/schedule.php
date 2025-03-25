<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

try {
    // Connect and retrieve data from DailyMasterScheduleDB
    $conn = DatabaseHelper::createConnection(DBCONNSTRING);
    $dmsGateway = new DailyMasterScheduleDB($conn);

    // Get all schedules
    $allSchedules = $dmsGateway->getAll();

    // Check if $allSchedules is valid before using it
    if (!is_array($allSchedules) || empty($allSchedules)) {
        throw new Exception("No schedules available in the database.");
    }

    // Markup for aside content
    $aside = "<h2>Select a Schedule</h2><form method='GET' action='" . $_SERVER['PHP_SELF'] . "'>";
    $aside .= "<ul>";

    // Store unique dates
    $dates = [];
    foreach ($allSchedules as $row) {
        if (!in_array($row['date'], $dates)) {
            $dates[] = $row['date'];
            $aside .= "<li><button type='submit' name='ref' value='" . htmlspecialchars($row['date']) . "'>" . htmlspecialchars($row['date']) . "</button></li>";
        }
    }
    $aside .= "</ul></form>";

    // Markup for main content
    $main = "";
    if (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $selectedDate = $_GET['ref'];
        $schedules = $dmsGateway->getDailyMasterSchedule($selectedDate);

        if (!empty($schedules) && is_array($schedules)) {
            $main .= "<section class='info'>
                        <h2>Schedule Details for " . htmlspecialchars($selectedDate) . "</h2>
                        <div class='grid'>";

            foreach ($schedules as $schedule) {
                if (!is_array($schedule)) continue; // ✅ Ensures $schedule is an array before accessing keys

                $staff_id = htmlspecialchars($schedule['staff_id'] ?? "N/A");
                $first_name = htmlspecialchars($schedule['first_name'] ?? "N/A");
                $last_name = htmlspecialchars($schedule['last_name'] ?? "N/A");
                $shift_start_time = htmlspecialchars($schedule['shift_start_time'] ?? "N/A");
                $shift_end_time = htmlspecialchars($schedule['shift_end_time'] ?? "N/A");
                $appointment_slots = htmlspecialchars($schedule['appointment_slots'] ?? "0");
                $walk_in_availability = htmlspecialchars($schedule['walk_in_availability'] ?? "N/A");

                $main .= "<div class='schedule'>
                            <p><strong>Staff ID: </strong>$staff_id</p>
                            <p><strong>Staff Name: </strong>$first_name $last_name</p>
                            <p><strong>Shift Start Time: </strong>$shift_start_time</p>
                            <p><strong>Shift End Time: </strong>$shift_end_time</p>
                            <p><strong>Appointment Slots: </strong>$appointment_slots</p>
                            <p><strong>Walk-in Availability: </strong>$walk_in_availability</p>
                          </div>";
            }
            $main .= "</div></section>";
        } else {
            $main .= "<p style='text-align:center;'>No schedules found for this date.</p>";
        }
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