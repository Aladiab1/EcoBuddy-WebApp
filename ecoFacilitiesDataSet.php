<?php
require_once('Database.php');
require_once('ecoFacilitiesData.php');

/**
 * Handles all database operations related to ecoFacilities.
 */
class ecoFacilitiesDataSet {
    protected $_dbHandle;

    public function __construct() {
        $this->_dbHandle = Database::getInstance()->getConnection();
    }

    /**
     * Fetches a list of facilities that match the search query, with pagination and optional sorting.
     */
    public function fetchFacilitiesBySearchWithPagination($searchQuery, $limit, $offset, $sortOrder = '') {
        $facilities = [];
        try {
            $orderBy = '';

            switch ($sortOrder) {
                case 'title_az':
                    $orderBy = 'ORDER BY title ASC';
                    break;
                case 'title_za':
                    $orderBy = 'ORDER BY title DESC';
                    break;
                case 'category_az':
                    $orderBy = 'ORDER BY category ASC';
                    break;
                case 'category_za':
                    $orderBy = 'ORDER BY category DESC';
                    break;
                case 'newest':
                    $orderBy = 'ORDER BY id DESC'; // Newest = highest ID
                    break;
                case 'oldest':
                    $orderBy = 'ORDER BY id ASC'; // Oldest = lowest ID
                    break;
            }

            $sqlQuery = "
                SELECT * FROM ecoFacilities
                WHERE title LIKE :search
                   OR category LIKE :search
                   OR description LIKE :search
                   OR postcode LIKE :search
                $orderBy
                LIMIT :limit OFFSET :offset
            ";

            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->bindValue(':search', '%' . $searchQuery . '%');
            $statement->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $statement->execute();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $facilities[] = new ecoFacilitiesData($row);
            }

        } catch (PDOException $e) {
            die("Failed to fetch facilities: " . $e->getMessage());
        }

        return $facilities;
    }

    /**
     * Returns the total number of facilities that match a search query.
     */
    public function countFacilitiesBySearch($searchQuery) {
        try {
            $sqlQuery = "
                SELECT COUNT(*) FROM ecoFacilities
                WHERE title LIKE :search
                   OR category LIKE :search
                   OR description LIKE :search
                   OR postcode LIKE :search
            ";

            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->bindValue(':search', '%' . $searchQuery . '%');
            $statement->execute();

            return (int) $statement->fetchColumn();

        } catch (PDOException $e) {
            die("Failed to count facilities: " . $e->getMessage());
        }
    }

    /**
     * Retrieves a single facility by its ID.
     */
    public function fetchFacilityById($id) {
        $sqlQuery = "SELECT * FROM ecoFacilities WHERE id = :id";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates a facility's details in the database.
     */
    public function updateFacility($id, $title, $category, $description, $houseNumber, $streetName,
                                   $county, $town, $postcode, $longitude, $latitude) {
        $query = "
            UPDATE ecoFacilities
            SET title = :title,
                category = :category,
                description = :description,
                houseNumber = :houseNumber,
                streetName = :streetName,
                county = :county,
                town = :town,
                postcode = :postcode,
                lng = :longitude,
                lat = :latitude
            WHERE id = :id
        ";

        $stmt = $this->_dbHandle->prepare($query);
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':category' => $category,
            ':description' => $description,
            ':houseNumber' => $houseNumber,
            ':streetName' => $streetName,
            ':county' => $county,
            ':town' => $town,
            ':postcode' => $postcode,
            ':longitude' => $longitude,
            ':latitude' => $latitude
        ]);
    }

    /**
     * Adds a new facility to the database.
     */
    public function addFacility($id, $title, $category, $description, $houseNumber, $streetName,
                                $county, $town, $postcode, $lng, $lat, $contributor) {
        try {
            $sqlQuery = "
                INSERT INTO ecoFacilities (
                    id, title, category, description, houseNumber, streetName,
                    county, town, postcode, lng, lat, contributor
                )
                VALUES (
                    :id, :title, :category, :description, :houseNumber, :streetName,
                    :county, :town, :postcode, :lng, :lat, :contributor
                )
            ";

            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute([
                ':id' => $id,
                ':title' => $title,
                ':category' => $category,
                ':description' => $description,
                ':houseNumber' => $houseNumber,
                ':streetName' => $streetName,
                ':county' => $county,
                ':town' => $town,
                ':postcode' => $postcode,
                ':lng' => $lng,
                ':lat' => $lat,
                ':contributor' => $contributor
            ]);

        } catch (PDOException $e) {
            error_log("Database error in addFacility: " . $e->getMessage());
            throw new Exception("Please check your input values.");
        }
    }

    /**
     * Deletes a facility from the database.
     */
    public function deleteFacility($id) {
        try {
            $sqlQuery = "DELETE FROM ecoFacilities WHERE id = :id";
            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
        } catch (PDOException $e) {
            throw new Exception("Failed to delete facility: " . $e->getMessage());
        }
    }

    /**
     * Retrieves all facilities with their most recent status comment.
     */
    public function fetchAllFacilities() {
        $facilities = [];

        try {
            $sqlQuery = "
                SELECT ecoFacilities.*, ecoFacilityStatus.statusComment
                FROM ecoFacilities
                LEFT JOIN ecoFacilityStatus ON ecoFacilityStatus.id = (
                    SELECT MAX(id)
                    FROM ecoFacilityStatus
                    WHERE facilityId = ecoFacilities.id
                )
            ";

            $statement = $this->_dbHandle->prepare($sqlQuery);
            $statement->execute();

            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                // Manually attach status for clarity
                $row['status'] = $row['statusComment'];
                $facilities[] = new ecoFacilitiesData($row);
            }

        } catch (PDOException $e) {
            die("Failed to fetch facilities: " . $e->getMessage());
        }

        return $facilities;
    }
}
