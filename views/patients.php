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
    return <<<HTML
    <form method="POST">
        <h3>Add Patient</h3>
        <label for="first_name">First Name:</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="insurance_provider">Insurance Provider:</label>
        <input type="text" id="insurance_provider" name="insurance_provider">

        <div class="button-group">
            <button type="submit" name="add_patient">Submit</button>
            <button type="reset">Clear</button>
        </div>
    </form>
HTML;
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
            <span onclick="closeModal('editModal{$patientId}')" class="close">&times;</span>
            <h2>Edit Patient</h2>
            <form method="POST">
                <input type="hidden" name="patient_id" value="{$patientId}">

                <div class="form-group">
                    <label for="first_name_{$patientId}">First Name:</label>
                    <input type="text" id="first_name_{$patientId}" name="first_name" value="{$firstName}" required>
                </div>

                <div class="form-group">
                    <label for="last_name_{$patientId}">Last Name:</label>
                    <input type="text" id="last_name_{$patientId}" name="last_name" value="{$lastName}" required>
                </div>

                <div class="form-group">
                    <label for="insurance_provider_{$patientId}">Insurance Provider:</label>
                    <input type="text" id="insurance_provider_{$patientId}" name="insurance_provider" value="{$insuranceProvider}">
                </div>

                <div class="button-group">
                    <button type="submit" name="update_patient">Update</button>
                    <button type="submit" name="delete_patient" style="background-color: red;">Delete</button>
                </div>
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
    <link rel="stylesheet" href="../css/styles.css">
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

        /* Styling for the Add Patient Form */
        form {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        form h3 {
            color: #0277bd;
            text-align: center;
            margin-bottom: 15px;
        }

        form label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }

        form input,
        form select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Ensure inputs fit within the form block */
        form input,
        form select {
            width: calc(100% - 20px); /* Adjust width to account for padding */
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box; /* Include padding and border in the element's total width */
        }

        form button {
            background-color: #0277bd;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        form button:hover {
            background-color: #01579b;
        }

        form .button-group {
            text-align: center;
            margin-top: 15px;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            overflow: auto;
            z-index: 1000; /* Ensure the modal is above other elements */
        }

        .modal-content {
            background-color: #ffffff; /* White background for the modal */
            margin: 5% auto;
            padding: 20px;
            width: 50%;
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
            text-align: left;
            font-family: Arial, sans-serif;
        }

        .modal-content h2 {
            color: #0277bd; /* Light blue heading */
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Add spacing between form elements */
        }

        .modal-content .form-group {
            display: flex;
            flex-direction: column;
        }

        .modal-content .form-group label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .modal-content .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .modal-content .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .modal-content .button-group button {
            background-color: #0277bd;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .modal-content .button-group button:hover {
            background-color: #01579b;
        }

        .modal-content .button-group button[style*="background-color: red"] {
            background-color: red;
            color: white;
        }

        .modal-content .button-group button[style*="background-color: red"]:hover {
            background-color: darkred;
        }

        .close {
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #ff0000; /* Red color on hover */
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