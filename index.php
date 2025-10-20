<?php
session_start();

// Generate a CSRF token for form security (only if not already set)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Prepare the $view object for passing data to the view
$view = new stdClass();
$view->pageTitle = 'Welcome to My EcoBuddy';

// Include model and controller logic
require_once('Models/ecoFacilitiesDataSet.php');
require_once('loginController.php');

// Handle query parameters (search, sort, and pagination)
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

$resultsPerPage = 10;
$offset = ($page - 1) * $resultsPerPage;

// Fetch facilities from the model with search, pagination, and sorting
$ecoFacilitiesDataSet = new ecoFacilitiesDataSet();
$view->facilities = $ecoFacilitiesDataSet->fetchFacilitiesBySearchWithPagination(
    $searchQuery,
    $resultsPerPage,
    $offset,
    $sortOrder
);

// Count total facilities to calculate total pages
$totalFacilities = $ecoFacilitiesDataSet->countFacilitiesBySearch($searchQuery);
$view->totalPages = ceil($totalFacilities / $resultsPerPage);
$view->currentPage = $page;
$view->searchQuery = $searchQuery;
$view->sortOrder = $sortOrder;

// Check if the current user is a manager (userType 1)
$view->isManager = isset($_SESSION['userType']) && $_SESSION['userType'] == 1;

// Render the view
require_once('Views/index.phtml');

