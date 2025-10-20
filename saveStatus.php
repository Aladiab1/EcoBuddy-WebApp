<?php
session_start();

require_once 'Models/Database.php';
require_once 'Models/ecoFacilitiesDataSet.php';

// Step 1: Check CSRF token is valid
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403); // Access denied
    echo "Invalid CSRF token.";
    exit;
}

// Step 2: Ensure required fields are present
if (!empty($_POST['facilityId']) && !empty($_POST['status'])) {
    $facilityId = filter_var($_POST['facilityId'], FILTER_SANITIZE_NUMBER_INT);
    $status = htmlspecialchars(trim($_POST['status']));

    // Step 3: Attempt to save status to the database
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    try {
        $query = "INSERT INTO ecoFacilityStatus (facilityId, statusComment)
                  VALUES (:facilityId, :statusComment)";

        $statement = $pdo->prepare($query);
        $statement->bindParam(':facilityId', $facilityId, PDO::PARAM_INT);
        $statement->bindParam(':statusComment', $status, PDO::PARAM_STR);

        if ($statement->execute()) {
            echo "Status updated successfully.";
        } else {
            echo "Failed to update status.";
        }

    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Database error in saveStatus.php: " . $e->getMessage());
        echo "An error occurred. Please try again.";
    }

} else {
    // One or more fields were missing
    echo "Invalid request.";
}
