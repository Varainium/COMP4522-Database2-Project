<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$patientDB = new PatientDB($conn);

$message = "";
try {
    $patients = $patientDB->getAll();
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

// Generate Add Patient Form
function generatePatientForm($insuranceProviders)
{
    $output = "<section><h3>Add Patient</h3>";
    $output .= "<form method='POST'>
                <label>First Name:</label>
                <input type='text' name='first_name' required>

                <label>Last Name:</label>
                <input type='text' name='last_name' required>

                <label>Insurance Provider:</label>
                <select name='insurance_provider' required>";

    foreach ($insuranceProviders as $provider) {
        $output .= "<option value='" . htmlspecialchars($provider) . "'>" . htmlspecialchars($provider) . "</option>";
    }

    $output .= "</select>
                <button type='submit' name='add_patient'>Submit</button>
                <button type='reset'>Clear</button>
            </form>
            </section>";

    return $output;
}

// Generate Patient Table
function generatePatientTable($patients)
{
    $output = "<section><h3>List of Patients</h3>";
    $output .= "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Insurance</th>
                    <th>Actions</th>
                </tr>";

    foreach ($patients as $patient) {
        $output .= "<tr>
                        <td>{$patient['patient_id']}</td>
                        <td>{$patient['first_name']} {$patient['last_name']}</td>
                        <td>" . (!empty($patient['insurance_provider']) ? $patient['insurance_provider'] : 'No Provider') . "</td>
                        <td><button onclick=\"openModal({$patient['patient_id']})\">View/Edit</button></td>
                    </tr>";

        $output .= generatePatientModal($patient);
    }

    $output .= "</table></section>";
    return $output;
}

// Generate Patient Modal
function generatePatientModal($patient)
{
    $patientId = $patient['patient_id'];
    $firstName = htmlspecialchars($patient['first_name']);
    $lastName = htmlspecialchars($patient['last_name']);
    $insurance = htmlspecialchars($patient['insurance_provider'] ?? 'No Provider');

    return <<<HTML
    <div id="modal{$patientId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal({$patientId})" style="cursor:pointer;">&times;</span>
            <h2>Edit Patient</h2>
            <form method="POST">
                <input type="hidden" name="patient_id" value="{$patientId}">
                <label>First Name:</label><input type="text" name="first_name" value="{$firstName}" required><br>
                <label>Last Name:</label><input type="text" name="last_name" value="{$lastName}" required><br>
                <label>Insurance Provider:</label><input type="text" name="insurance_provider" value="{$insurance}"><br>
                <button type="submit" name="update_patient">Update</button>
                <button type="submit" name="delete_patient">Delete</button>
            </form>
        </div>
    </div>
HTML;
}

// Fetch unique insurance providers
$insuranceProviders = array_filter(array_unique(array_column($patients, 'insurance_provider')), function ($provider) {
    return !empty($provider);
});
if (!in_array('No Provider', $insuranceProviders)) {
    $insuranceProviders[] = 'No Provider';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Management</title>
    <link rel="stylesheet" href="styles.css">
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
        <?= generatePatientForm($insuranceProviders); ?>
        <?= generatePatientTable($patients); ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>