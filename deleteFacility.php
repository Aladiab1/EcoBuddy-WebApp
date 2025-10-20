<?php
// Start the session so we can access user details
session_start();

// Include the model to handle facility deletion
require_once('Models/ecoFacilitiesDataSet.php');

// Check if there's a valid facility ID in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $facilityId = (int) $_GET['id'];

    // Only allows the managers to delete facilities (userType 1 = manager)
    if (isset($_SESSION['userType']) && $_SESSION['userType'] == 1) {
        try {
            // Attempt to delete the facility
            $ecoFacilitiesDataSet = new ecoFacilitiesDataSet();
            $ecoFacilitiesDataSet->deleteFacility($facilityId);

            // Redirect back to the main page once it's deleted
            header('Location: index.php');
            exit();
        } catch (Exception $e) {
            // Show an error message if there is something goes wrong
            echo "Error deleting facility: " . $e->getMessage();
        }
    } else {
        // If the user isn't a manager, deny the access
        echo "You are not authorised to delete facilities.";
    }
} else {
    // No ID or invalid one
    echo "Invalid facility ID.";
}
