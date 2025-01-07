<link href="<?php echo getURL('view/css/colorfulText.css'); ?>" rel="stylesheet" type="text/css" />
<style>
.row-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
<?php
$checked = '';
if (!empty($_COOKIE['forKids'])) {
    $checked = 'checked';
}
?>
<!-- For Kids Toggle -->
<label for="forKids" class="row-label singleLineMenu hideIfCompressed" style="padding: 5px;">
    <?php
    echo createColorfulTextSpans(__('For Kids'));
    ?>
    <div class="material-switch" style="margin-right: 5px;">
        <input type="checkbox" value="1" id="forKids" <?php echo $checked; ?> onchange="toggleForKids();">
        <label for="forKids" class="label-success"></label>
    </div>
</label>

<script>
    function isValidYearOfBirth(yearOfBirth) {
        var currentYear = new Date().getFullYear();
        var enteredYear = parseInt(yearOfBirth);
        var age = currentYear - enteredYear;

        // Check if the year is a valid number, within the last 120 years, and not in the future
        if (!isNaN(enteredYear) && enteredYear >= (currentYear - 120) && enteredYear <= currentYear) {
            return age >= 14; // Return true if the age is 14 or older, otherwise false
        }
        return false;
    }

    // Function to prompt the user for year of birth until a valid one is entered
    function promptForYearOfBirth() {
        swal({
            title: "Age Required",
            text: "To continue, please verify your year of birth",
            content: {
                element: "input",
                attributes: {
                    type: "number",
                    placeholder: "YYYY"
                }
            },
            button: {
                text: "Continue",
                closeModal: false
            }
        }).then(function(yearOfBirth) {
            if (!yearOfBirth) {
                $('#forKids').prop('checked', true); // User canceled, revert checkbox
                swal.stopLoading();
                swal.close();
                return;
            }

            if (!isValidYearOfBirth(yearOfBirth)) {                
                // If yearOfBirth is already set, check age directly
                var age = new Date().getFullYear() - parseInt(yearOfBirth);
                swal({
                    title: "Invalid Year",
                    text: age < 14 
                        ? "You must be at least 14 years old to disable this setting." 
                        : "Please enter a valid year (must be within the last 120 years).",
                    icon: "warning",
                    button: {
                        text: "Try Again",
                        closeModal: true
                    }
                }).then(function() {
                    promptForYearOfBirth(); // Re-prompt the user
                });
                return;
            }

            // Save the year of birth as a session cookie (expires when the browser is closed)
            Cookies.set('yearOfBirth', parseInt(yearOfBirth), {
                path: '/'
            });

            // Proceed with toggling the state
            swal.stopLoading();
            swal.close();
            modal.showPleaseWait();
            Cookies.set('forKids', 0, {
                path: '/',
                expires: 365
            });
            location.reload();
        }).catch(function(err) {
            if (err !== null) {
                // If an error occurred, show the prompt again
                promptForYearOfBirth();
            } else {
                swal.stopLoading();
                swal.close();
            }
        });
    }

    function toggleForKids() {
        var forKids = Cookies.get('forKids');
        var yearOfBirth = Cookies.get('yearOfBirth');

        if (forKids == 1) { // Only prompt for age if the user is currently in "For Kids" mode and trying to disable it
            if (typeof yearOfBirth === 'undefined' || !isValidYearOfBirth(yearOfBirth)) {
                // Call the function to prompt for the year of birth
                promptForYearOfBirth();
            } else {
                // Proceed with toggling the state
                modal.showPleaseWait();
                Cookies.set('forKids', forKids == 1 ? 0 : 1, {
                    path: '/',
                    expires: 365
                });
                location.reload();
            }
        } else {
            // If it's not in "For Kids" mode, simply toggle the state
            modal.showPleaseWait();
            Cookies.set('forKids', forKids == 1 ? 0 : 1, {
                path: '/',
                expires: 365
            });
            location.reload();
        }
    }

    // Automatically set the checkbox state based on the cookie value
    $(document).ready(function() {
        var forKids = Cookies.get('forKids');
        $('#forKids').prop('checked', forKids == 1);
    });
</script>
