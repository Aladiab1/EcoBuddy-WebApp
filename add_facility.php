<?php
// Bring in the model for interacting with the ecoFacilities table
require_once('Models/ecoFacilitiesDataSet.php');

// Start session if it hasn't already
session_start();

// Generate CSRF token if one doesn’t exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Blocks the access if the user isn't logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

// Set up a view object and title for the page
$view = new stdClass();
$view->pageTitle = 'Add Facility';

// Set up session messages if not already set
if (!isset($_SESSION['message'])) $_SESSION['message'] = '';
if (!isset($_SESSION['errorMessage'])) $_SESSION['errorMessage'] = '';

// If the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check the CSRF token to help prevent cross-site request attacks
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['errorMessage'] = "Invalid CSRF token!";
        header('Location: add_facility.php');
        exit;
    }

    // Sanitise and validate the form inputs
    $id = filter_var($_POST['facilityId'], FILTER_SANITIZE_NUMBER_INT);
    $title = htmlspecialchars(trim($_POST['facilityTitle']));
    $category = filter_var($_POST['facilityCategory'], FILTER_SANITIZE_NUMBER_INT);
    $description = htmlspecialchars(trim($_POST['facilityDescription']));
    $houseNumber = htmlspecialchars(trim($_POST['facilityHouseNumber']));
    $streetName = htmlspecialchars(trim($_POST['facilityStreetName']));
    $county = htmlspecialchars(trim($_POST['facilityCounty']));
    $town = htmlspecialchars(trim($_POST['facilityTown']));
    $postcode = htmlspecialchars(trim($_POST['facilityPostcode']));
    $lng = filter_var($_POST['facilityLng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $lat = filter_var($_POST['facilityLat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $contributor = isset($_SESSION['userID']) ? (int) $_SESSION['userID'] : 0;

    // Basic validation checks to make sure that nothing important is missing or dodgy
    if (
        $title === '' ||
        !is_numeric($category) ||
        $description === '' ||
        $houseNumber === '' || !preg_match('/^[a-zA-Z0-9\s\-\/]{1,10}$/', $houseNumber) ||
        $streetName === '' ||
        $county === '' ||
        $town === '' ||
        $postcode === '' ||
        $lng === '' || !is_numeric($lng) ||
        $lat === '' || !is_numeric($lat) ||
        $contributor <= 0
    ) {
        $_SESSION['errorMessage'] = "All fields are required and must be valid.";
        header('Location: add_facility.php');
        exit();
    }

    // If it passed all checks, try to insert the facility into the database
    try {
        $ecoFacilitiesDataSet = new ecoFacilitiesDataSet();
        $ecoFacilitiesDataSet->addFacility(
            $id, $title, $category, $description,
            $houseNumber, $streetName, $county, $town,
            $postcode, $lng, $lat, $contributor
        );

        $_SESSION['message'] = "Facility added successfully!";
    } catch (Exception $e) {
        $_SESSION['errorMessage'] = "Error adding facility: " . $e->getMessage();
    }
}

// Pass any session messages to the view so we can display them nicely
$view->message = $_SESSION['message'];
$view->errorMessage = $_SESSION['errorMessage'];

// Clear messages after displaying once
$_SESSION['message'] = '';
$_SESSION['errorMessage'] = '';

// Finally, show the page
require_once('Views/add_facility.phtml');
