<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

try {
    $conn = DatabaseHelper::createConnection(DBCONNSTRING);
    $reports = new ReportsDB($conn);

    $activityReport = $reports->getMonthlyActivityReport();
    $statementReport = $reports->getPatientMonthlyStatement();
    $practitionerReport = $reports->getMonthlyPractitionerReport();

    $main = "";

    // Generate Patient Monthly Statement Table
    function generatePatientMonthlyStatementTable($statementReport)
    {
        $output = "<h2 style='text-align:center;'>Patient Monthly Statement</h2>";
        $output .= "<table border='1' cellspacing='0' cellpadding='8' style='margin:auto; width: 90%;'>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Statement ID</th>
                    <th>Statement Date</th>
                    <th>Total Fee</th>
                    <th>Paid</th>
                    <th>Outstanding Balance</th>
                </tr>
            </thead><tbody>";

        if (!empty($statementReport)) {
            foreach ($statementReport as $row) {
                $output .= "<tr>
                    <td>{$row['patient_id']}</td>
                    <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                    <td>{$row['statement_id']}</td>
                    <td>{$row['statement_date']}</td>
                    <td>$" . number_format($row['total_fee'], 2) . "</td>
                    <td>$" . number_format($row['total_paid'], 2) . "</td>
                    <td>$" . number_format($row['outstanding_balance'], 2) . "</td>
                </tr>";
            }
        } else {
            $output .= "<tr><td colspan='7' style='text-align:center;'>No statements available.</td></tr>";
        }

        $output .= "</tbody></table>";
        return $output;
    }

    // Generate Monthly Activity Report Table
    function generateMonthlyActivityReportTable($activityReport)
    {
        $output = "<h2 style='text-align:center;'>Monthly Activity Report</h2>";
        $output .= "<table border='1' cellspacing='0' cellpadding='8' style='margin:auto; width: 90%;'>
        <thead>
            <tr>
                <th>Month</th>
                <th>Patient Appointments</th>
                <th>Deliveries</th>
                <th>Lab Tests</th>
                <th>Recoveries</th>
                <th>Avg Appointment Duration (min)</th>
            </tr>
        </thead><tbody>";

        if (!empty($activityReport)) {
            foreach ($activityReport as $row) {
                $output .= "<tr>
                    <td>" . htmlspecialchars($row['month_year']) . "</td>
                    <td>{$row['total_appointments']}</td>
                    <td>{$row['total_deliveries']}</td>
                    <td>{$row['total_lab_tests']}</td>
                    <td>{$row['total_recoveries']}</td>
                    <td>" . ($row['avg_appointment_duration'] ?? 'N/A') . "</td>
                </tr>";
            }
        } else {
            $output .= "<tr><td colspan='6' style='text-align:center;'>No activity data available.</td></tr>";
        }

        $output .= "</tbody></table>";
        return $output;
    }

    // Generate Monthly Practitioner Report Table
    function generateMonthlyPractitionerReportTable($practitionerReport)
    {
        $output = "<h2 style='text-align:center;'>Monthly Practitioner Report</h2>";
        $output .= "<table border='1' cellspacing='0' cellpadding='8' style='margin:auto; width: 90%;'>
        <thead>
            <tr>
                <th>Month</th>
                <th>Practitioner</th>
                <th>Total Appointments</th>
                <th>Total Prescriptions</th>
                <th>Total Lab Tests</th>
                <th>Total Deliveries</th>
                <th>Total Recoveries</th>
                <th>Total Revenue</th>
                <th>Avg Appointment Duration (min)</th>
            </tr>
        </thead><tbody>";

        if (!empty($practitionerReport)) {
            foreach ($practitionerReport as $row) {
                $output .= "<tr>
                    <td>" . htmlspecialchars($row['month_year']) . "</td>
                    <td>" . htmlspecialchars($row['practitioner_name']) . "</td>
                    <td>{$row['total_appointments']}</td>
                    <td>{$row['total_prescriptions']}</td>
                    <td>{$row['total_lab_tests']}</td>
                    <td>{$row['total_deliveries']}</td>
                    <td>{$row['total_recoveries']}</td>
                    <td>$" . number_format($row['total_revenue'], 2) . "</td>
                    <td>" . ($row['avg_appointment_duration'] ?? 'N/A') . "</td>
                </tr>";
            }
        } else {
            $output .= "<tr><td colspan='9' style='text-align:center;'>No practitioner data available.</td></tr>";
        }

        $output .= "</tbody></table>";
        return $output;
    }

    $main .= generatePatientMonthlyStatementTable($statementReport);
    $main .= generateMonthlyActivityReportTable($activityReport);
    $main .= generateMonthlyPractitionerReportTable($practitionerReport);
} catch (Exception $e) {
    $main = "<p style='color:red; text-align:center;'>Error generating reports: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Wellness Clinic</title>
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
                <li><a href="<?php echo BASE_URL; ?>/views/appointments.php">Appointments</a></li>
                <li><a href="<?php echo BASE_URL; ?>/views/prescription.php">Prescriptions</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="main">
            <?= $main; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>