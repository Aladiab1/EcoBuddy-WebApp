<?php

/**
 * Class ecoFacilitiesData
 *
 * Represents a single eco-facility record pulled from the database.
 * This class encapsulates all relevant facility fields and provides
 * getter methods for safe access.
 */
class ecoFacilitiesData {

    // Properties holding the facility's data
    protected $_id;
    protected $_title;
    protected $_category;
    protected $_description;
    protected $_houseNumber;
    protected $_streetName;
    protected $_county;
    protected $_town;
    protected $_postcode;
    protected $_lng; // Longitude
    protected $_lat; // Latitude
    protected $_contributor;
    protected $_status;

    /**
     * Constructor method
     *
     * Takes a database row (as an associative array) and maps it to this object.
     *
     * @param array $dbRow A row of facility data from the database
     */
    public function __construct($dbRow) {
        $this->_id = $dbRow['id'];
        $this->_title = $dbRow['title'];
        $this->_category = $dbRow['category'];
        $this->_description = $dbRow['description'];
        $this->_houseNumber = $dbRow['houseNumber'];
        $this->_streetName = $dbRow['streetName'];
        $this->_county = $dbRow['county'];
        $this->_town = $dbRow['town'];
        $this->_postcode = $dbRow['postcode'];
        $this->_lng = $dbRow['lng'];
        $this->_lat = $dbRow['lat'];
        $this->_contributor = $dbRow['contributor'];

        // If a status comment exists, use it; otherwise provide a fallback
        $this->_status = $dbRow['statusComment'] ?? 'No status yet';
    }

    // ---------- Getters for each field ----------

    public function getId() {
        return $this->_id;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getCategory() {
        return $this->_category;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getHouseNumber() {
        return $this->_houseNumber;
    }

    public function getStreetName() {
        return $this->_streetName;
    }

    public function getCounty() {
        return $this->_county;
    }

    public function getTown() {
        return $this->_town;
    }

    public function getPostcode() {
        return $this->_postcode;
    }

    public function getLng() {
        return $this->_lng;
    }

    public function getLat() {
        return $this->_lat;
    }

    public function getContributor() {
        return $this->_contributor;
    }

    public function getStatus() {
        return $this->_status;
    }
}
