<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "teachers_companion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function insertData($table, $data, $conn)
{
    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    return $conn->query($sql);
}

function getData($table, $conn)
{
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function deleteData($table, $id, $conn)
{
    $sql = "DELETE FROM $table WHERE id=$id";
    return $conn->query($sql);
}

function updateData($table, $data, $id, $conn)
{
    $updates = [];
    foreach ($data as $key => $value) {
        $updates[] = "$key='$value'";
    }
    $updates_string = implode(", ", $updates);
    $sql = "UPDATE $table SET $updates_string WHERE id=$id";
    return $conn->query($sql);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $type = $_POST['type'];
        $form_type = $_POST['form_type'];

        if ($form_type === 'class_charge_details') {
            $class = $_POST['class'];
            $strength = $_POST['strength'];
            $year = $_POST['year'];
            $internalexam_proposed_date = $_POST['internalexam_proposed_date'];
            $internalexam_actual_date = $_POST['internalexam_actual_date'];
            $first_internal = $_POST['first_internal'];
            $model = $_POST['model'];
            $test_papers = $_POST['test_papers'];

            $table = ($type === 'odd') ? 'class_charge_details_oddsem_tb' : 'class_charge_details_evensem_tb';
            $data = [
                'class' => $class,
                'strength' => $strength,
                'year' => $year,
                'internalexam_proposed_date' => $internalexam_proposed_date,
                'internalexam_actual_date' => $internalexam_actual_date,
                'first_internal' => $first_internal,
                'model' => $model,
                'test_papers' => $test_papers
            ];
        } elseif (in_array($form_type, ['outstanding_students', 'weak_students', 'remedial_students', 'late_comers'])) {
            $student_name = $_POST['student_name'];
            $department = $_POST['department'];
            $semester = $_POST['semester'];
            $course = $_POST['course'];
            $marks_obtained = $_POST['marks_obtained'] ?? null;
            $reason = $_POST['reason'] ?? null;
            $action_taken = $_POST['action_taken'] ?? null;

            if ($form_type === 'outstanding_students') {
                $table = ($type === 'odd') ? 'outstanding_students_oddsem_tb' : 'outstanding_students_evensem_tb';
                $data = [
                    'student_name' => $student_name,
                    'department' => $department,
                    'semester' => $semester,
                    'course' => $course,
                    'marks_obtained' => $marks_obtained
                ];
            } elseif ($form_type === 'weak_students') {
                $table = ($type === 'odd') ? 'weak_students_oddsem_tb' : 'weak_students_evensem_tb';
                $data = [
                    'student_name' => $student_name,
                    'department' => $department,
                    'semester' => $semester,
                    'course' => $course,
                    'reason' => $reason,
                    'action_taken' => $action_taken
                ];
            } elseif ($form_type === 'remedial_students') {
                $table = ($type === 'odd') ? 'remedial_students_oddsem_tb' : 'remedial_students_evensem_tb';
                $data = [
                    'student_name' => $student_name,
                    'department' => $department,
                    'semester' => $semester,
                    'course' => $course,
                    'reason' => $reason,
                    'action_taken' => $action_taken
                ];
            } elseif ($form_type === 'late_comers') {
                $table = 'latecomers_oddsem_tb';
                $data = [
                    'student_name' => $student_name,
                    'department' => $department,
                    'semester' => $semester,
                    'reason' => $reason,
                    'action_taken' => $action_taken
                ];
            } elseif ($form_type === 'late_comers') {
                $table = 'latecomers_evensem_tb';
                $data = [
                    'student_name' => $student_name,
                    'department' => $department,
                    'semester' => $semester,
                    'reason' => $reason,
                    'action_taken' => $action_taken
                ];
            }
        }

        if (insertData($table, $data, $conn)) {
            header("Location: class_charge_details.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif ($_POST['action'] === 'delete') {
        $table = $_POST['table'];
        $id = $_POST['id'];
        if (deleteData($table, $id, $conn)) {
            header("Location: class_charge_details.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif ($_POST['action'] === 'update') {
        $table = $_POST['table'];
        $id = $_POST['id'];
        $data = $_POST['data'];
        if (updateData($table, $data, $id, $conn)) {
            header("Location: class_charge_details.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

$class_charge_odd = getData('class_charge_details_oddsem_tb', $conn);
$class_charge_even = getData('class_charge_details_evensem_tb', $conn);
$outstanding_odd = getData('outstanding_students_oddsem_tb', $conn);
$outstanding_even = getData('outstanding_students_evensem_tb', $conn);
$weak_odd = getData('weak_students_oddsem_tb', $conn);
$weak_even = getData('weak_students_evensem_tb', $conn);
$remedial_odd = getData('remedial_students_oddsem_tb', $conn);
$remedial_even = getData('remedial_students_evensem_tb', $conn);
$latecomers_odd = getData('latecomers_oddsem_tb', $conn);
$latecomers_even = getData('latecomers_evensem_tb', $conn);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Charge Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .app-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .app-bar .app-bar-left {
            display: flex;
            align-items: center;
        }

        .app-bar .app-bar-left .college-logo {
            width: 50px;
            height: auto;
            margin-right: 10px;
        }

        .app-bar .app-bar-left .welcome-message {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .navbar ul {
            list-style-type: none;
            display: flex;
        }

        .navbar ul li {
            margin-left: 15px;
        }

        .navbar ul li a {
            text-decoration: none;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .navbar ul li a:hover {
            background-color: #45a049;
        }

        .navbar ul li a.active {
            background-color: #45a049;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        .form-container {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .form-box {
            max-width: 600px;
            margin: auto;
        }

        .form-box h3 {
            margin-bottom: 20px;
            text-align: center;
        }

        .form-box label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form-box input,
        .form-box select,
        .form-box textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .form-box button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-box button:hover {
            background-color: #45a049;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th,
        .table-container td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table-container th {
            background-color: #f2f2f2;
        }

        .table-container tr:hover {
            background-color: #f9f9f9;
        }

        .actions {
            display: flex;
            align-items: center;
        }

        .actions button {
            margin-left: 10px;
            background-color: #f44336;
        }

        .actions button:hover {
            background-color: #e53935;
        }
    </style>
</head>

<body>
    <header class="app-bar">
        <div class="app-bar-left">
            <img class="college-logo" src="assets\img\amallogo.jpeg" alt="College Logo">
            <div class="welcome-message">Welcome, <?php echo $_SESSION['user']['name']; ?>!</div>
        </div>
        <nav class="navbar">
            <ul>
                <li><a href="http://localhost/Teachers%20Companion%20-%20Amal%20College/">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Class Charge Details</h2>

        <div class="form-container">
            <div class="form-box">
                <h3>Add Class Charge Details</h3>
                <form action="class_charge_details.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="form_type" value="class_charge_details">
                    <label for="type">Type:</label>
                    <select name="type" id="type">
                        <option value="odd">Odd Semester</option>
                        <option value="even">Even Semester</option>
                    </select>
                    <label for="class">Class:</label>
                    <input type="text" id="class" name="class" required>
                    <label for="strength">Strength:</label>
                    <input type="text" id="strength" name="strength" required>
                    <label for="year">Year:</label>
                    <input type="text" id="year" name="year" required>
                    <label for="internalexam_proposed_date">Internal Exam Proposed Date:</label>
                    <input type="date" id="internalexam_proposed_date" name="internalexam_proposed_date" required>
                    <label for="internalexam_actual_date">Internal Exam Actual Date:</label>
                    <input type="date" id="internalexam_actual_date" name="internalexam_actual_date" required>
                    <label for="first_internal">First Internal:</label>
                    <input type="text" id="first_internal" name="first_internal" required>
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" required>
                    <label for="test_papers">Test Papers:</label>
                    <input type="text" id="test_papers" name="test_papers" required>
                    <button type="submit">Add Class Charge</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h3>Odd Semester Class Charge Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Strength</th>
                        <th>Year</th>
                        <th>Internal Exam Proposed Date</th>
                        <th>Internal Exam Actual Date</th>
                        <th>First Internal</th>
                        <th>Model</th>
                        <th>Test Papers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($class_charge_odd as $class): ?>
                        <tr>
                            <td><?php echo $class['class']; ?></td>
                            <td><?php echo $class['strength']; ?></td>
                            <td><?php echo $class['year']; ?></td>
                            <td><?php echo $class['internalexam_proposed_date']; ?></td>
                            <td><?php echo $class['internalexam_actual_date']; ?></td>
                            <td><?php echo $class['first_internal']; ?></td>
                            <td><?php echo $class['model']; ?></td>
                            <td><?php echo $class['test_papers']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="class_charge_details_oddsem_tb">
                                    <input type="hidden" name="id" value="<?php echo $class['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Even Semester Class Charge Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Strength</th>
                        <th>Year</th>
                        <th>Internal Exam Proposed Date</th>
                        <th>Internal Exam Actual Date</th>
                        <th>First Internal</th>
                        <th>Model</th>
                        <th>Test Papers</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($class_charge_even as $class): ?>
                        <tr>
                            <td><?php echo $class['class']; ?></td>
                            <td><?php echo $class['strength']; ?></td>
                            <td><?php echo $class['year']; ?></td>
                            <td><?php echo $class['internalexam_proposed_date']; ?></td>
                            <td><?php echo $class['internalexam_actual_date']; ?></td>
                            <td><?php echo $class['first_internal']; ?></td>
                            <td><?php echo $class['model']; ?></td>
                            <td><?php echo $class['test_papers']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="class_charge_details_evensem_tb">
                                    <input type="hidden" name="id" value="<?php echo $class['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <div class="form-box">
                <h3>Add Outstanding Students</h3>
                <form action="class_charge_details.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="form_type" value="outstanding_students">
                    <label for="student_name">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" required>
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>
                    <label for="semester">Semester:</label>
                    <input type="text" id="semester" name="semester" required>
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" required>
                    <label for="marks_obtained">Marks Obtained:</label>
                    <input type="text" id="marks_obtained" name="marks_obtained" required>
                    <button type="submit">Add Outstanding Student</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h3>Odd Semester Outstanding Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Marks Obtained</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($outstanding_odd as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['marks_obtained']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="outstanding_students_oddsem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Even Semester Outstanding Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Marks Obtained</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($outstanding_even as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['marks_obtained']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="outstanding_students_evensem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <div class="form-box">
                <h3>Add Weak Students</h3>
                <form action="class_charge_details.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="form_type" value="weak_students">
                    <label for="student_name">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" required>
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>
                    <label for="semester">Semester:</label>
                    <input type="text" id="semester" name="semester" required>
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" required>
                    <label for="reason">Reason:</label>
                    <textarea id="reason" name="reason" rows="3" required></textarea>
                    <label for="action_taken">Action Taken:</label>
                    <textarea id="action_taken" name="action_taken" rows="3" required></textarea>
                    <button type="submit">Add Weak Student</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h3>Odd Semester Weak Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Reason</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weak_odd as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['reason']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="weak_students_oddsem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Even Semester Weak Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Reason</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weak_even as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['reason']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="weak_students_evensem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <div class="form-box">
                <h3>Add Remedial Students</h3>
                <form action="class_charge_details.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="form_type" value="remedial_students">
                    <label for="student_name">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" required>
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>
                    <label for="semester">Semester:</label>
                    <input type="text" id="semester" name="semester" required>
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" required>
                    <label for="reason">Reason:</label>
                    <textarea id="reason" name="reason" rows="3" required></textarea>
                    <label for="action_taken">Action Taken:</label>
                    <textarea id="action_taken" name="action_taken" rows="3" required></textarea>
                    <button type="submit">Add Remedial Student</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h3>Odd Semester Remedial Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Reason</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($remedial_odd as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['reason']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="remedial_students_oddsem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Even Semester Remedial Students</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Reason</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($remedial_even as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['reason']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="remedial_students_evensem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-container">
            <div class="form-box">
                <h3>Add Latecomers</h3>
                <form action="class_charge_details.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="form_type" value="latecomers">
                    <label for="student_name">Student Name:</label>
                    <input type="text" id="student_name" name="student_name" required>
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" required>
                    <label for="semester">Semester:</label>
                    <input type="text" id="semester" name="semester" required>
                    <label for="course">Course:</label>
                    <input type="text" id="course" name="course" required>
                    <label for="late_hours">Late Hours:</label>
                    <input type="text" id="late_hours" name="late_hours" required>
                    <label for="action_taken">Action Taken:</label>
                    <textarea id="action_taken" name="action_taken" rows="3" required></textarea>
                    <button type="submit">Add Latecomer</button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <h3>Odd Semester Latecomers</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Late Hours</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latecomers_odd as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['late_hours']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="latecomers_oddsem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <h3>Even Semester Latecomers</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Late Hours</th>
                        <th>Action Taken</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latecomers_even as $student): ?>
                        <tr>
                            <td><?php echo $student['student_name']; ?></td>
                            <td><?php echo $student['department']; ?></td>
                            <td><?php echo $student['semester']; ?></td>
                            <td><?php echo $student['course']; ?></td>
                            <td><?php echo $student['late_hours']; ?></td>
                            <td><?php echo $student['action_taken']; ?></td>
                            <td class="actions">
                                <form action="class_charge_details.php" method="POST">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="table" value="latecomers_evensem_tb">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>