<?php
require_once 'Models/ecoFacilitiesDataSet.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if search term is provided
if (!isset($_GET['searchTerm']) || empty(trim($_GET['searchTerm']))) {
    echo json_encode([]);
    exit;
}

$searchTerm = trim($_GET['searchTerm']);

// Use the existing ecoFacilitiesDataSet class
$dataSet = new ecoFacilitiesDataSet();
$facilities = $dataSet->fetchFacilitiesBySearchWithPagination($searchTerm, 10, 0); // limit 20 results for performance

// Prepare data
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
