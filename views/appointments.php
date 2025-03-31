<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
$appointmentDB = new AppointmentDB($conn);
$patientDB = new PatientDB($conn);
$staffDB = new StaffDB($conn);

$message = "";
$appointments = $appointmentDB->getAll();

// Fetch patients and practitioners for dropdowns
$patients = $patientDB->getAll();
$practitioners = $staffDB->getPractitioners();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Create a New Appointment
        if (isset($_POST['add_appointment'])) {
            $patientId = $_POST['patient_id'];
            $practitionerId = $_POST['practitioner_id'];
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $appointmentType = $_POST['appointment_type'];
            $reason = $_POST['reason'];

            $appointmentDB->addAppointment($patientId, $practitionerId, $appointmentDate, $appointmentTime, $appointmentType, $reason);
            $message = "<p style='color:green;'>Appointment created successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Update an Appointment
        if (isset($_POST['update_appointment'])) {
            $appointmentId = $_POST['appointment_id'];
            $patientId = $_POST['patient_id'];
            $practitionerId = $_POST['practitioner_id'];
            $appointmentDate = $_POST['appointment_date'];
            $appointmentTime = $_POST['appointment_time'];
            $appointmentType = $_POST['appointment_type'];
            $reason = $_POST['reason'];
            $status = $_POST['status'];

            $appointmentDB->updateAppointment($appointmentId, $patientId, $practitionerId, $appointmentDate, $appointmentTime, $appointmentType, $reason, $status);
            $message = "<p style='color:green;'>Appointment updated successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Delete an Appointment
        if (isset($_POST['delete_appointment'])) {
            $appointmentId = $_POST['appointment_id'];
            $appointmentDB->deleteAppointment($appointmentId);
            $message = "<p style='color:red;'>Appointment deleted successfully!</p>";
            header("Location: appointments.php");
            exit;
        }

        // Generate Billing Statement
        else if (isset($_POST['generate_bill'])) {
            $appointmentId = $_POST['appointment_id'];
            $practitionerId = $_POST['practitioner_id'];
            $totalFee = $_POST['total_fee'];
            $insurancePaid = $_POST['insurance_paid'] ?? 0.00;
            $patientDue = $_POST['patient_due'];
            $paymentMethod = $_POST['payment_method'];

            try {
                $appointmentDB->generateBillingStatement($appointmentId, $practitionerId, $totalFee, $insurancePaid, $patientDue, $paymentMethod);

                // Display an alert box on successful bill generation
                echo "<script>alert('Billing Statement Generated Successfully!');</script>";

                // Redirect to avoid form resubmission on refresh
                echo "<script>window.location.href = 'appointments.php';</script>";
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
            }
        }
    }
} catch (Exception $e) {
    $message = "<p style='color:red;'>{$e->getMessage()}</p>";
}

function generateAppointmentForm($patients, $practitioners)
{
    $output = "<section><h2>Add Appointment</h2>
    <form method='POST'>
        <label>Patient:</label>
        <select name='patient_id' required>";

    foreach ($patients as $patient) {
        $output .= "<option value='{$patient['patient_id']}'>{$patient['first_name']} {$patient['last_name']}</option>";
    }

    $output .= "</select>
        <label>Practitioner:</label>
        <select name='practitioner_id' required>";

    foreach ($practitioners as $practitioner) {
        $output .= "<option value='{$practitioner['staff_id']}'>{$practitioner['first_name']} {$practitioner['last_name']}</option>";
    }

    $output .= "</select>
        <label>Date:</label>
        <input type='date' name='appointment_date' required>
        <label>Time:</label>
        <input type='time' name='appointment_time' required>
        <label>Type:</label>
            <select name='appointment_type' required>
                <option value='Scheduled'>Scheduled</option>
                <option value='Walk-In'>Walk-In</option>
            </select>
        <label>Reason:</label>
            <textarea name='reason'></textarea>
        <button type='submit' name='add_appointment'>Add Appointment</button>
    </form></section>";

    return $output;
}

function generateAppointmentsTable($appointments, $patients, $practitioners)
{
    $output = "<section><h2>Appointments</h2>
    <table border='1'>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Practitioner</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
        </tr>";

    foreach ($appointments as $appointment) {
        $appointmentId = $appointment['appointment_id'];
        $practitionerId = $appointment['practitioner_id'];
        $output .= "<tr>
            <td>{$appointmentId}</td>
            <td>{$appointment['patient_name']}</td>
            <td>{$appointment['practitioner_name']}</td>
            <td>{$appointment['appointment_date']}</td>
            <td>{$appointment['appointment_time']}</td>
            <td>
                <button onclick=\"openModal('billModal{$appointmentId}')\">Generate Bill</button>
                <button onclick=\"openModal('editModal{$appointmentId}')\">Edit</button>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='appointment_id' value='{$appointmentId}'>
                    <button type='submit' name='delete_appointment'>Delete</button>
                </form>
            </td>
        </tr>";

        $output .= generateBillingModal($appointmentId, $practitionerId);
        $output .= generateEditModal($appointment, $patients, $practitioners);
    }

    $output .= "</table></section>";
    return $output;
}

function generateEditModal($appointment, $patients, $practitioners)
{
    $appointmentId = $appointment['appointment_id'];
    $patientId = $appointment['patient_id'];
    $practitionerId = $appointment['practitioner_id'];
    $date = $appointment['appointment_date'];
    $time = $appointment['appointment_time'];
    $appointmentType = $appointment['appointment_type'];
    $reason = htmlspecialchars($appointment['reason'] ?? '');
    $status = $appointment['status'] ?? 'Pending';

    // Generate patient dropdown options
    $patientOptions = "";
    foreach ($patients as $patient) {
        $selected = ($patient['patient_id'] == $patientId) ? "selected" : "";
        $patientOptions .= "<option value='{$patient['patient_id']}' {$selected}>{$patient['first_name']} {$patient['last_name']}</option>";
    }

    // Generate practitioner dropdown options
    $practitionerOptions = "";
    foreach ($practitioners as $practitioner) {
        $selected = ($practitioner['staff_id'] == $practitionerId) ? "selected" : "";
        $practitionerOptions .= "<option value='{$practitioner['staff_id']}' {$selected}>{$practitioner['first_name']} {$practitioner['last_name']}</option>";
    }

    // Generate appointment type options
    $typeOptions = "";
    $types = ['Scheduled', 'Walk-In'];
    foreach ($types as $type) {
        $selected = ($type === $appointmentType) ? "selected" : "";
        $typeOptions .= "<option value='{$type}' {$selected}>{$type}</option>";
    }

    // Generate status options
    $statusOptions = "";
    $statuses = ['Pending', 'Completed', 'Cancelled'];
    foreach ($statuses as $statusOption) {
        $selected = ($statusOption === $status) ? "selected" : "";
        $statusOptions .= "<option value='{$statusOption}' {$selected}>{$statusOption}</option>";
    }

    return <<<HTML
    <div id="editModal{$appointmentId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal('editModal{$appointmentId}')" class="close">&times;</span>
            <h2>Edit Appointment</h2>
            <form method="POST">
                <input type="hidden" name="appointment_id" value="{$appointmentId}">
                
                <label>Patient:</label>
                <select name="patient_id" required>{$patientOptions}</select><br>
                
                <label>Practitioner:</label>
                <select name="practitioner_id" required>{$practitionerOptions}</select><br>
                
                <label>Date:</label>
                <input type="date" name="appointment_date" value="{$date}" required><br>
                
                <label>Time:</label>
                <input type="time" name="appointment_time" value="{$time}" required><br>

                <label>Appointment Type:</label>
                <select name="appointment_type" required>{$typeOptions}</select><br>

                <label>Reason:</label>
                <textarea name="reason">{$reason}</textarea><br>

                <label>Status:</label>
                <select name="status" required>{$statusOptions}</select><br>

                <button type="submit" name="update_appointment">Update Appointment</button>
            </form>
        </div>
    </div>
HTML;
}

function generateBillingModal($appointmentId, $practitionerId)
{
    return <<<HTML
    <div id="billModal{$appointmentId}" class="modal">
        <div class="modal-content">
            <span onclick="closeModal('billModal{$appointmentId}')" class="close">&times;</span>
            <h2>Generate Bill for Appointment ID: {$appointmentId}</h2>
            <form method="POST">
                <input type="hidden" name="appointment_id" value="{$appointmentId}">
                <input type="hidden" name="practitioner_id" value="{$practitionerId}">
                <label>Total Fee:</label><input type="number" name="total_fee" step="0.01" required><br>
                <label>Insurance Paid:</label><input type="number" name="insurance_paid" step="0.01" value="0.00" required><br>
                <label>Patient Due:</label><input type="number" name="patient_due" step="0.01" required><br>
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="Out-of-Pocket">Out-of-Pocket</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Government">Government</option>
                </select><br>
                <button type="submit" name="generate_bill">Generate Bill</button>
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
    <title>Appointments</title>
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

        .close {
            float: right;
            cursor: pointer;
            font-size: 20px;
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
    <?= $message ?>

    <header>
        <h1>Appointments</h1>
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
        <?= generateAppointmentForm($patients, $practitioners); ?>
        <?= generateAppointmentsTable($appointments, $patients, $practitioners); ?>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellness Clinic Project</p>
    </footer>
</body>

</html>