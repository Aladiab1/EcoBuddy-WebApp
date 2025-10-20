<?php
require_once 'Models/ecoFacilitiesDataSet.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get search and page parameters from AJAX request
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10; // Adjust as needed
$offset = ($page - 1) * $itemsPerPage;

$dataSet = new ecoFacilitiesDataSet();

// Fetch filtered facilities
$facilities = $dataSet->fetchFacilitiesBySearchWithPagination($searchTerm, $itemsPerPage, $offset);

// Prepare data for response
$response = [];

foreach ($facilities as $facility) {
    $response[] = [
        'id' => $facility->getId(),
        'title' => $facility->getTitle(),
        'category' => $facility->getCategory(),
        'description' => $facility->getDescription(),
        'houseNumber' => $facility->getHouseNumber(),
        'streetName' => $facility->getStreetName(),
        'county' => $facility->getCounty(),
        'town' => $facility->getTown(),
        'postcode' => $facility->getPostcode(),
        'lng' => $facility->getLng(),
        'lat' => $facility->getLat(),
        'contributor' => $facility->getContributor(),
        'status' => $facility->getStatus() // include status
    ];
}

// Output JSON response
echo json_encode($response);
