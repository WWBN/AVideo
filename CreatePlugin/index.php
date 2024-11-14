<?php
require_once __DIR__ . '/../videos/configuration.php';

if (!User::isAdmin()) {
    forbiddenPage('You Must be admin');
}

if (!empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('Configuration disabled');
}

$page = new Page('Create plugin');
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2>Create New Plugin</h2>
        </div>
        <div class="panel-body">
            <form id="createPluginForm">
                <div class="form-group">
                    <label for="pluginName">Plugin Name</label>
                    <input type="text" class="form-control" id="pluginName" name="pluginName" placeholder="Enter Plugin Name" required>
                </div>
                <div class="form-group">
                    <label for="createTableSQL">CREATE TABLE SQL</label>
                    <textarea class="form-control" id="createTableSQL" name="createTableSQL" rows="5" placeholder="Paste your CREATE TABLE SQL here" required></textarea>
                </div>
                <button type="button" id="createPluginButton" class="btn btn-primary btn-block">Create Plugin</button>
            </form>
            <div id="responseMessage" class="alert" style="display:none; margin-top: 10px;"></div>
        </div>
        <div class="panel-footer">
            <h3>Instructions:</h3>
            <ul>
                <li>Enter the SQL code to create your plugin's table in the text area below.</li>
                <li>Provide a name for the plugin. The name must start with an uppercase letter, contain only letters, numbers, and underscores, and should have no spaces.</li>
                <li>Invalid characters will be automatically removed as you type.</li>
                <li>Click "Create Plugin" to submit the data.</li>
            </ul>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Auto-format plugin name input
        $('#pluginName').on('input', function() {
            let value = $(this).val();
            // Remove invalid characters and ensure the first letter is uppercase
            value = value.replace(/[^a-zA-Z0-9_]/g, ''); // Remove any character that is not a letter, number, or underscore
            if (value.length > 0) {
                value = value.charAt(0).toUpperCase() + value.slice(1); // Ensure the first letter is uppercase
            }
            $(this).val(value);
        });

        // AJAX submit on button click
        $('#createPluginButton').on('click', function() {
            let pluginName = $('#pluginName').val();
            let createTableSQL = $('#createTableSQL').val();

            if (pluginName && createTableSQL) {
                $.ajax({
                    url: webSiteRootURL + 'CreatePlugin/create.json.php',
                    type: 'POST',
                    data: {
                        pluginName: pluginName,
                        createTableSQL: createTableSQL
                    },
                    success: function(response) {
                        $('#responseMessage').removeClass('alert-danger').addClass('alert-success').text('Plugin created successfully!').show();
                    },
                    error: function() {
                        $('#responseMessage').removeClass('alert-success').addClass('alert-danger').text('An error occurred while creating the plugin.').show();
                    }
                });
            } else {
                $('#responseMessage').removeClass('alert-success').addClass('alert-danger').text('Please fill in both fields.').show();
            }
        });
    });
</script>
<?php
$page->print();
?>