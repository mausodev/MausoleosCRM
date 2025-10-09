<?php
session_start();
require './controlador/conexion.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all requests
error_log("get_employee.php called with: " . print_r($_GET, true));

if (!isset($_GET['id'])) {
    error_log("Missing employee ID");
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing employee ID']);
    exit;
}

$employee_id = $_GET['id'];
error_log("Fetching employee with ID: " . $employee_id);

try {
    // Simple query to test
    $query = "SELECT * FROM empleado WHERE id = ?";
    $stmt = $con->prepare($query);

    if (!$stmt) {
        throw new Exception("Database prepare error: " . $con->error);
    }

    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Employee not found with ID: " . $employee_id);
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Employee not found']);
        exit;
    }

    $employee = $result->fetch_assoc();
    error_log("Employee data retrieved: " . json_encode($employee));

    // Return employee data as JSON
    header('Content-Type: application/json');
    echo json_encode($employee);
    
} catch (Exception $e) {
    error_log("Error in get_employee.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} 