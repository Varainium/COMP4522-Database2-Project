<?php
// Required to connect to the wellness clinic database
require_once "../includes/config.inc.php";
require_once "../includes/db-classes.inc.php";

$conn = DatabaseHelper::createConnection(DBCONNSTRING);
// Instantiate the PatientDB class using the established connection
$patientDB = new PatientDB($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_patient'])) {
        $patientID = $_POST['patient_id'];
        $patientDB->deletePatient($patientID);
    } elseif (isset($_POST['update_patient'])) {
        // $firstName = $_POST['first_name'];
        // $lastName = $_POST['last_name'];
        $patientID = $_POST['patient_id'];
        $insuranceProvider = $_POST['insurance_provider'];
        $patientDB->updatePatient($patientID, $insuranceProvider);
    } elseif (isset($_POST['add_patient'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $insuranceProvider = $_POST['insurance_provider'];
        $existingPatient = $patientDB->findPatient($firstName, $lastName, $insuranceProvider);
        if ($existingPatient) {
            $errorMsg = "A patient with the same name already exists.";
            
        } else {
            $patientDB->addPatient($firstName, $lastName, $insuranceProvider);
        }
    }
}

$patients = $patientDB->getAll();

// Extract unique insurance providers and filter out empty values
$insuranceProviders = array_filter(array_unique(array_column($patients, 'insurance_provider')), function($provider) {
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
</head>
<body>
    <header>
        <h1>Wellness Clinic</h1>
        <nav>
        <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="views/schedule.php">Schedules</a></li>
                <li><a href="views/reports.php">Reports</a></li>
                <li><a href="views/staff.php">Staff</a></li>
                <li><a href="views/patients.php">Patients</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Manage Patients</h2>

        <!-- Error Message Display -->
        <?php if (!empty($errorMsg)): ?>
            <div style="color: red; font-weight: bold;">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- Add Patient Form -->
        <section>
            <h3>Add Patient</h3>
            <form method="POST">
                <label>First Name:</label>
                <input type="text" name="first_name" required>
                
                <label>Last Name:</label>
                <input type="text" name="last_name" required>
                
                <label>Insurance Provider:</label>
                <select name="insurance_provider" required>
                    <?php foreach ($insuranceProviders as $provider): ?>
                        <option value="<?= htmlspecialchars($provider) ?>"><?= htmlspecialchars($provider) ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" name="add_patient">Submit</button>
                <button type="reset">Clear</button>
            </form>
        </section>

        <!-- Patient List -->
        <section>
            <h3>List of Patients</h3>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Insurance</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?= htmlspecialchars($patient['patient_id']) ?></td>
                        <td><?= htmlspecialchars($patient['first_name']) ?></td>
                        <td><?= htmlspecialchars($patient['last_name']) ?></td>
                        <td><?= htmlspecialchars(!empty($patient['insurance_provider']) ? $patient['insurance_provider'] : 'No Provider') ?></td>
                        <td>
                            <!-- Remove Button -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
                                <button type="submit" name="delete_patient" onclick="return confirm('Are you sure?');">Remove</button>
                            </form>

                            <!-- Update Button (Opens Dropdown) -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
                                <select name="insurance_provider">
                                    <?php foreach ($insuranceProviders as $provider): ?>
                                        <option value="<?= htmlspecialchars($provider) ?>" <?= $patient['insurance_provider'] == $provider ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($provider) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_patient">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; Wellness Clinic Project</p>
    </footer>
</body>
</html>