function checkScriptBlocking() {
    const url = 'https://imasdk.googleapis.com/js/sdkloader/ima3.js';

    // Fetching the script with 'no-cors' mode
    fetch(url, { mode: 'no-cors' })
        .then(response => {
            console.log('IMA SDK fetched (no-cors mode). Status might not be verifiable.', response);
            // Since we can't check the status or content size, we assume it's not blocked if it gets here
            blockAdBlock.onDetected(adBlockDetected); // Continue with non-blocking logic
        })
        .catch(error => {
            console.log('Error fetching IMA SDK:', error.message);
            avideoToastError('IMA not loaded'); // Display error toast
            adBlockDetected(); // Assume blocked if there's an error
        });
}
document.addEventListener('DOMContentLoaded', checkScriptBlocking);