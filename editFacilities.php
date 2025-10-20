<?php
require_once('Models/ecoFacilitiesDataSet.php');

session_start();

//  Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialise the view object and set the page title
$view = new stdClass();
$view->pageTitle = 'Edit Facility';

// Initialise session messages
if (!isset($_SESSION['message'])) $_SESSION['message'] = '';
if (!isset($_SESSION['errorMessage'])) $_SESSION['errorMessage'] = '';

// Validate facility ID
if (!isset($_GET['id'])) {
    die('Facility ID not provided.');
}

$facilityId = intval($_GET['id']);
$ecoFacilitiesDataSet = new ecoFacilitiesDataSet();
$facility = $ecoFacilitiesDataSet->fetchFacilityById($facilityId);

if (!$facility) {
    die('Facility not found.');
}

// CSRF & form handling inside POST block only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']
    ) {
        $_SESSION['errorMessage'] = "Invalid CSRF token!";
        header("Location: editFacilities.php?id=" . $facilityId);
        exit;
    }

    // [Sanitize inputs as before...]
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

    if (
        $title === '' ||
        !is_numeric($category) ||
        $description === '' ||
        !preg_match('/^[0-9]+[a-zA-Z0-9\s\-\/]*$/', $houseNumber) ||
        $streetName === '' ||
        $county === '' ||
        $town === '' ||
        $postcode === '' ||
        $lng === '' || !is_numeric($lng) ||
        $lat === '' || !is_numeric($lat)
    ) {
        $_SESSION['errorMessage'] = "All fields are required and must be valid.";
        header("Location: editFacilities.php?id=" . $facilityId);
        exit;
    }

    try {
        $ecoFacilitiesDataSet->updateFacility(
            $facilityId,
            $title,
            $category,
            $description,
            $houseNumber,
            $streetName,
            $county,
            $town,
            $postcode,
            $lng,
            $lat
        );
        $_SESSION['message'] = "Facility updated successfully!";
    } catch (Exception $e) {
        $_SESSION['errorMessage'] = "Error updating facility: " . $e->getMessage();
    }
}

// Pass messages and facility to the view
$view->message = $_SESSION['message'];
$view->errorMessage = $_SESSION['errorMessage'];
$_SESSION['message'] = '';
$_SESSION['errorMessage'] = '';
$view->facility = $facility;
// REFRESH the facility object after update so the form shows latest values
$view->facility = $ecoFacilitiesDataSet->fetchFacilityById($facilityId);

require_once('Views/editFacilities.phtml');
