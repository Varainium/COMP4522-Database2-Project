<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$prescriptionDB = new PrescriptionDB($conn);
$message = "";

try {
    $prescriptions = $prescriptionDB->getAll();
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

// Fetch prescriptions
function fetchPrescriptions($prescriptions)
{
    return !empty($prescriptions) ? $prescriptions : [];
}

// Generate Prescription Table
function generatePrescriptionTable($prescriptions)
{
    $output = "<h2>Prescription List</h2>";
    $output .= "<table border='1' cellspacing='0' cellpadding='8' style='margin:auto; width: 90%;'>
        <thead>
            <tr>
                <th>Prescription ID</th>
                <th>Patient</th>
                <th>Practitioner</th>
                <th>Drug</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead><tbody>";

    if (!empty($prescriptions)) {
        foreach ($prescriptions as $prescription) {
            $output .= "<tr>
                <td>{$prescription['prescription_id']}</td>
                <td>{$prescription['patient_first_name']} {$prescription['patient_last_name']}</td>
                <td>{$prescription['practitioner_first_name']} {$prescription['practitioner_last_name']}</td>
                <td>{$prescription['drug_name']}</td>
                <td>{$prescription['quantity']}</td>
                <td><button onclick=\"openModal({$prescription['prescription_id']})\">View</button></td>
            </tr>";

            $output .= generatePrescriptionModal($prescription);
        }
    } else {
        $output .= "<tr><td colspan='6' style='text-align:center;'>No prescriptions available.</td></tr>";
    }

    $output .= "</tbody></table>";
    return $output;
}

// Generate Prescription Modal
function generatePrescriptionModal($prescription)
{
    $prescriptionId = $prescription['prescription_id'];

    return <<<HTML
    <div id="modal{$prescriptionId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal({$prescriptionId})" style="cursor:pointer;">&times;</span>
            <h2>Prescription Details</h2>
            <p><strong>Prescription ID:</strong> {$prescription['prescription_id']}</p>
            <p><strong>Patient:</strong> {$prescription['patient_first_name']} {$prescription['patient_last_name']}</p>
            <p><strong>Practitioner:</strong> {$prescription['practitioner_first_name']} {$prescription['practitioner_last_name']}</p>
            <p><strong>Drug Name:</strong> {$prescription['drug_name']}</p>
            <p><strong>Quantity:</strong> {$prescription['quantity']}</p>
            <p><strong>Refill:</strong> {$prescription['refill']}</p>
            <p><strong>Instructions:</strong> {$prescription['instructions']}</p>
            <p><strong>Date Prescribed:</strong> {$prescription['date_prescribed']}</p>
        </div>
    </div>
HTML;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Management</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
        }
    </style>
    <script>
        function openModal(id) {
            document.getElementById('modal' + id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById('modal' + id).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</head>

<body>
    <?= $message ?>
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
        <?= generatePrescriptionTable(fetchPrescriptions($prescriptions)); ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>