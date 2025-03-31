<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$patientDB = new PatientDB($conn);

$message = "";
$patients = [];

try {
    $patients = $patientDB->getAll();
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

// Handle Adding a New Patient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_patient'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $insuranceProvider = $_POST['insurance_provider'];

        try {
            if ($patientDB->checkPatient($firstName, $lastName, $insuranceProvider)) {
                $message = "<p style='color:red;'>Patient already exists!</p>";
            } else {
                $patientDB->addPatient($firstName, $lastName, $insuranceProvider);
                $message = "<p style='color:green;'>Patient added successfully!</p>";
            }
        } catch (Exception $e) {
            $message = "<p style='color:red;'>{$e->getMessage()}</p>";
        }
    }

    // Handle Updating a Patient
    else if (isset($_POST['update_patient'])) {
        $patientId = $_POST['patient_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $insuranceProvider = $_POST['insurance_provider'];

        try {
            $patientDB->updatePatient($patientId, $firstName, $lastName, $insuranceProvider);
            $message = "<p style='color:green;'>Patient updated successfully!</p>";
        } catch (Exception $e) {
            $message = "<p style='color:red;'>{$e->getMessage()}</p>";
        }

        header("Location: patients.php");
        exit;
    }


    // Handle Deleting a Patient
    else if (isset($_POST['delete_patient'])) {
        $patientId = $_POST['patient_id'];

        try {
            $patientDB->deletePatient($patientId);
            $message = "<p style='color:red;'>Patient deleted successfully!</p>";
        } catch (Exception $e) {
            $message = "<p style='color:red;'>{$e->getMessage()}</p>";
        }
    }

    header("Location: patients.php");
    exit;
}

/// Generate Add Patient Form
function generatePatientForm()
{
    $output = "<section><h3>Add Patient</h3>";
    $output .= "<form method='POST'>
                <label>First Name:</label>
                <input type='text' name='first_name' required>

                <label>Last Name:</label>
                <input type='text' name='last_name' required>

                <label>Insurance Provider:</label>
                <input type='text' name='insurance_provider'>

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
        $patientId = $patient['patient_id'];
        $insuranceProvider = !empty($patient['insurance_provider']) ? $patient['insurance_provider'] : "No Provider";
        $output .= "<tr>
                        <td>{$patientId}</td>
                        <td>{$patient['first_name']} {$patient['last_name']}</td>
                        <td>{$insuranceProvider}</td>                        
                        <td>
                            <button onclick=\"openModal('editModal{$patientId}')\">Edit</button>
                        </td>
                    </tr>";

        $output .= generateEditModal($patient);
    }

    $output .= "</table></section>";
    return $output;
}

// Generate Edit Modal
function generateEditModal($patient)
{
    $patientId = $patient['patient_id'];
    $firstName = htmlspecialchars($patient['first_name']);
    $lastName = htmlspecialchars($patient['last_name']);
    $insuranceProvider = htmlspecialchars($patient['insurance_provider'] ?? 'No Provider');

    return <<<HTML
    <div id="editModal{$patientId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal('editModal{$patientId}')" style="cursor:pointer;">&times;</span>
            <h2>Edit Patient</h2>
            <form method="POST">
                <input type="hidden" name="patient_id" value="{$patientId}">
                
                <label>First Name:</label>
                <input type="text" name="first_name" value="{$firstName}" required><br>
                
                <label>Last Name:</label>
                <input type="text" name="last_name" value="{$lastName}" required><br>
                
                <label>Insurance Provider:</label>
                <input type="text" name="insurance_provider" value="{$insuranceProvider}"><br>

                <button type="submit" name="update_patient">Update</button>
                <button type="submit" name="delete_patient">Delete</button>
            </form>
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
            document.getElementById(id).style.display = 'block';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>

</head>

<body>
    <?php if (!empty($message)): ?>
        <div id="messageBox" style="padding: 10px; margin: 10px; border: 1px solid #000;">
            <?= $message; ?>
        </div>
    <?php endif; ?>
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
        <?= generatePatientForm(); ?>
        <?= generatePatientTable($patients); ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>