/**
 * It handles the interactive Leaflet map and displays the facility markers.
 */
class EcoMap {
    constructor(mapId, dataUrl) {
        this.mapId = mapId;
        this.dataUrl = dataUrl;
        this.map = null;
        this.markers = [];

        // Sets everything up as soon as the class is created
        this.initMap();
    }

    /**
     * It loads and initialises the map with a default view over Manchester.
     */
    initMap() {
        const defaultLat = 53.4808;
        const defaultLng = -2.2426;

        this.map = L.map(this.mapId).setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(this.map);

        this.locateUser();     // Try to get user's current position
        this.loadFacilities(); // Load facilities from the database
    }

    /**
     * If allowed, it recentres the map to the user's location.
     */
    locateUser() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    this.map.setView([userLat, userLng], 13);

                    L.marker([userLat, userLng])
                        .addTo(this.map)
                        .bindPopup('You are here!')
                        .openPopup();
                },
                () => {
                    console.warn("Geolocation denied or not available.");
                }
            );
        } else {
            console.warn("Browser doesn't support geolocation.");
        }
    }

    /**
     * Fetches the facilities from the backend and places them on the map.
     */
    loadFacilities() {
        const ecoIcon = L.icon({
            iconUrl: 'images/recycle.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

        fetch(this.dataUrl)
            .then(response => response.json())
            .then(data => {
                data.forEach(facility => {
                    if (facility.lat && facility.lng) {
                        const marker = L.marker([facility.lat, facility.lng], { icon: ecoIcon }).addTo(this.map);

                        marker.bindPopup(`
                            <div class="popup-box">
                                <h5 class="popup-title">${facility.title}</h5>
                                <p class="popup-description">${facility.description}</p>
                                <ul class="popup-details">
                                    <li><strong>Address:</strong> ${facility.houseNumber} ${facility.streetName}</li>
                                    <li><strong>Town:</strong> ${facility.town}</li>
                                    <li><strong>County:</strong> ${facility.county}</li>
                                    <li><strong>Postcode:</strong> ${facility.postcode}</li>
                                </ul>
                                <p class="popup-status">
                                    <strong>Current status:</strong>
                                    <em id="status-text-${facility.id}">${facility.status ? facility.status : 'No status yet'}</em>
                                </p>
                                <div class="popup-form mt-2">
                                    <input type="text" id="status-${facility.id}" class="form-control mb-2" placeholder="Enter status">
                                    <button class="btn btn-sm btn-success w-100" onclick="submitStatus(${facility.id})">Submit Status</button>
                                    <div id="message-${facility.id}" class="popup-message"></div>
                                </div>
                            </div>
                        `);
                    }
                });
            })
            .catch(error => console.error('Error loading facilities:', error));
    }

    /**
     * Clears all the facility markers from the map.
     */
    clearMarkers() {
        this.markers.forEach(marker => this.map.removeLayer(marker));
        this.markers = [];
    }
}

/**
 * It sends the status update to the server for the given facility.
 */
function submitStatus(facilityId) {
    const statusInput = document.getElementById(`status-${facilityId}`);
    const status = statusInput.value.trim();

    if (!status) {
        alert("Please enter a status before submitting.");
        return;
    }

    const formData = new FormData();
    formData.append('facilityId', facilityId);
    formData.append('status', status);
    formData.append('csrf_token', csrfToken);

    const messageControl = document.getElementById(`message-${facilityId}`);
    if (messageControl) {
        messageControl.textContent = 'Submitting...';
        messageControl.style.color = 'black';
        messageControl.style.fontWeight = 'normal';
    }

    fetch('saveStatus.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(() => {
            setTimeout(() => {
                if (messageControl) {
                    messageControl.textContent = 'Review submitted ✓';
                    messageControl.style.color = 'green';
                    messageControl.style.fontWeight = 'bold';

                    setTimeout(() => {
                        messageControl.classList.add('fade-out');
                        setTimeout(() => {
                            messageControl.textContent = '';
                            messageControl.classList.remove('fade-out');
                        }, 1000);
                    }, 3000);
                }

                const statusText = document.getElementById(`status-text-${facilityId}`);
                if (statusText) {
                    statusText.textContent = status;
                }

                statusInput.value = '';

                // Refresh markers so that the status updates visually
                window.ecoMap.clearMarkers();
                window.ecoMap.loadFacilities();
            }, 500);
        })
        .catch(error => {
            console.error('Error submitting status:', error);
            alert("An error occurred while saving the status.");
        });
}

/**
 * Runs live search and applies the selected filter.
 */
function liveSearchFacilities(searchTerm) {
    const trimmedSearchTerm = searchTerm.trim();
    const sortValue = document.getElementById("sort").value;

    const searchUrl = `index.php?search=${encodeURIComponent(trimmedSearchTerm)}&sort=${encodeURIComponent(sortValue)}&page=1`;

    fetch(searchUrl)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableBody = doc.querySelector('.table-responsive tbody');
            const newPagination = doc.querySelector('.pagination');

            const tableBody = document.querySelector('.table-responsive tbody');
            const paginationContainer = document.querySelector('.pagination');

            if (newTableBody && tableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            if (paginationContainer) {
                if (newPagination && newPagination.children.length > 1) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                    paginationContainer.style.display = 'flex';
                } else {
                    paginationContainer.innerHTML = '';
                    paginationContainer.style.display = 'none';
                }
            }

            attachPaginationEventListeners(); // Reapply link actions
        })
        .catch(error => console.error('Live search fetch error:', error));
}

/**
 * Updates the table rows using facility data — not actively used with live HTML replacement.
 */
function updateFacilitiesTable(facilities) {
    const tableBody = document.querySelector('.table-responsive tbody');

    if (!tableBody) {
        console.error('Table body not found.');
        return;
    }

    tableBody.innerHTML = '';

    if (facilities.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="13">No facilities found.</td></tr>`;
        return;
    }

    facilities.forEach(facility => {
        let row = `<tr>`;
        if (isManager) {
            row += `
                <td>
                    <a href="editFacilities.php?id=${facility.id}" class="btn btn-warning btn-sm mb-1">Edit</a>
                    <a href="deleteFacility.php?id=${facility.id}" class="btn btn-danger btn-sm">Delete</a>
                </td>`;
        }
        row += `
            <td>${facility.id}</td>
            <td>${facility.title}</td>
            <td>${facility.category}</td>
            <td>${facility.description}</td>
            <td>${facility.houseNumber}</td>
            <td>${facility.streetName}</td>
            <td>${facility.county}</td>
            <td>${facility.town}</td>
            <td>${facility.postcode}</td>
            <td>${facility.lng}</td>
            <td>${facility.lat}</td>
            <td>${facility.contributor}</td>
        </tr>`;

        tableBody.innerHTML += row;
    });
}

/**
 * Applies the event listeners to pagination links to enable AJAX navigation.
 */
function attachPaginationEventListeners() {
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', handleAjaxPaginationClick);
    });
}

/**
 * Loads a new page of results when a pagination link is clicked.
 */
function handleAjaxPaginationClick(event) {
    event.preventDefault();

    const url = event.target.href;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTableBody = doc.querySelector('.table-responsive tbody');
            const newPagination = doc.querySelector('.pagination');

            const tableBody = document.querySelector('.table-responsive tbody');
            const paginationContainer = document.querySelector('.pagination');

            if (newTableBody && tableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }

            if (newPagination && paginationContainer) {
                paginationContainer.innerHTML = newPagination.innerHTML;
            }

            attachPaginationEventListeners();
        })
        .catch(error => console.error('Pagination fetch error:', error));
}

/**
 * Once the page loads, attach necessary listeners for filters and pagination.
 */
document.addEventListener('DOMContentLoaded', () => {
    attachPaginationEventListeners();

    const sortDropdown = document.getElementById("sort");
    const searchInput = document.getElementById("liveSearchInput");

    if (sortDropdown && searchInput) {
        sortDropdown.addEventListener("change", () => {
            liveSearchFacilities(searchInput.value);
        });
    }
});
