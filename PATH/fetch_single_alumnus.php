<?php
// FILE: fetch_single_alumnus.php
session_start(); // Start the session

// Check if user is logged in AND is an Admin, otherwise stop execution
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    // Return a JSON error response if unauthorized
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Check if alumni_id was provided via POST
if (!isset($_POST['alumni_id']) || empty($_POST['alumni_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Alumni ID not provided.']);
    exit();
}

$alumni_id = $_POST['alumni_id'];

// Include your database connection file
require 'db.php'; // Adjust the path if necessary

// --- Construct the SQL Query to Fetch Single Alumnus Data ---
$sql = "SELECT
            a.employment_status,
            a.alumni_id,
            a.student_id,
            a.last_name,
            a.first_name,
            a.middle_name, -- Include middle name
            a.email, -- Include email
            a.college_and_course,
            a.graduation_year,
            t.thesis_group_id, -- Select the thesis_group_id
            t.title AS thesis_title -- Select the thesis title
        FROM
            alumni a
        LEFT JOIN
            thesis t ON a.alumni_id = t.alumni_id
        WHERE
            a.alumni_id = ?"; // Filter by the provided Alumni ID

// --- Prepare and Execute the Statement ---
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle prepare error
    error_log("Prepare failed (fetch single): (" . $conn->errno . ") " . $conn->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error preparing query.']);
    $conn->close();
    exit();
}

$stmt->bind_param("s", $alumni_id); // Bind the alumni_id parameter
$stmt->execute();
$result = $stmt->get_result(); // Get the result set

// --- Fetch and Return Data ---
header('Content-Type: application/json'); // Set content type to JSON

if ($result->num_rows > 0) {
    $alumnus_data = $result->fetch_assoc();
    // Return the data as a JSON object
    echo json_encode(['success' => true, 'data' => $alumnus_data]);
} else {
    // Alumnus not found
    echo json_encode(['success' => false, 'message' => 'Alumnus not found.']);
}

$stmt->close();
$conn->close(); // Close the database connection
?>
