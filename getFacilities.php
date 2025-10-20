<?php
require_once('Models/ecoFacilitiesDataSet.php');

// Set response type to JSON
header('Content-Type: application/json');

// Instantiate the dataset model
$ecoFacilitiesDataSet = new ecoFacilitiesDataSet();

// Fetch all facility data (including latest status)
$facilities = $ecoFacilitiesDataSet->fetchAllFacilities();

// Build an array formatted for JSON output
$facilitiesArray = [];

foreach ($facilities as $facility) {
    $facilitiesArray[] = [
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
        'status' => $facility->getStatus() // Latest status from joined table
    ];
}

// Return the data as a JSON response
echo json_encode($facilitiesArray);
