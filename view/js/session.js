// Create a variable to hold the session ID
var PHPSESSID = null;

// Function to load the session ID via AJAX
function loadPHPSessionID() {
    fetch(webSiteRootURL + 'objects/phpsessionid.json.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            PHPSESSID = data.phpsessid; // Assign the session ID to the variable
            console.log('PHPSESSID loaded:', PHPSESSID); // You can remove this in production
        })
        .catch(error => {
            console.error('Error loading PHPSESSID:', error);
        });
}
// Load the session ID as fast as possible
window.addEventListener('DOMContentLoaded', loadPHPSessionID);