<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$dmsGateway = new DailyMasterScheduleDB($conn);

$message = "";
$allSchedules = $dmsGateway->getAll();

// Store unique dates
$dates = [];
foreach ($allSchedules as $row) {
    if (!in_array($row['date'], $dates)) {
        $dates[] = $row['date'];
    }
}

// Function to generate Aside Schedule Dates
function generateAside($dates)
{
    $output = "<h2>Select a Date</h2><ul>";

    foreach ($dates as $date) {
        $output .= "<li><button type='button' onclick=\"openModal('$date')\">" . htmlspecialchars($date) . "</button></li>";
    }

    $output .= "</ul>";
    return $output;
}

// Function to generate Main Content Table
function generateMainContent($allSchedules)
{
    $output = "<h2>Schedule List</h2><table border='1'><tr><th>Date</th><th>Staff ID</th><th>Staff Name</th><th>Shift Start</th><th>Shift End</th></tr>";

    foreach ($allSchedules as $schedule) {
        $output .= "<tr>
            <td>{$schedule['date']}</td>
            <td>{$schedule['staff_id']}</td>
            <td>{$schedule['first_name']} {$schedule['last_name']}</td>
            <td>{$schedule['shift_start_time']}</td>
            <td>{$schedule['shift_end_time']}</td>
        </tr>";
    }

    $output .= "</table>";
    return $output;
}

// Function to generate Modal for each date
function generateModal($date, $dmsGateway)
{
    $schedules = $dmsGateway->getDailyMasterSchedule($date);

    $output = "<div class='modal' id='modal_$date'>
                <div class='modal-content'>
                    <span onclick=\"closeModal('$date')\" class='close'>&times;</span>
                    <h2>Schedule Details for $date</h2>";

    if (!empty($schedules)) {
        $output .= "<div class='grid'>";
        foreach ($schedules as $schedule) {
            $output .= "<div class='schedule'>
                            <p><strong>Staff ID:</strong> {$schedule['staff_id']}</p>
                            <p><strong>Name:</strong> {$schedule['first_name']} {$schedule['last_name']}</p>
                            <p><strong>Shift Start:</strong> {$schedule['shift_start_time']}</p>
                            <p><strong>Shift End:</strong> {$schedule['shift_end_time']}</p>
                            <p><strong>Appointment Slots:</strong> {$schedule['appointment_slots']}</p>
                            <p><strong>Walk-in Availability:</strong> " . ($schedule['walk_in_availability'] ? 'Yes' : 'No') . "</p>
                        </div>";
        }
        $output .= "</div>";
    } else {
        $output .= "<p>No schedules found for this date.</p>";
    }

    $output .= "</div></div>";
    return $output;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Clinic - Schedules</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            border-radius: 8px;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
    <script>
        function openModal(date) {
            document.getElementById('modal_' + date).style.display = 'block';
        }

        function closeModal(date) {
            document.getElementById('modal_' + date).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
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
                <li><a href="<?php echo BASE_URL; ?>/views/prescription.php">Prescriptions</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?= $message ?>
        <aside>
            <?= generateAside($dates) ?>
        </aside>
        <div class="main-content">
            <?= generateMainContent($allSchedules) ?>
        </div>

        <!-- Generate Modals for All Dates -->
        <?php foreach ($dates as $date): ?>
            <?= generateModal($date, $dmsGateway) ?>
        <?php endforeach; ?>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>