<?php
require_once __DIR__ . '/../videos/configuration.php';

if (!User::isAdmin()) {
    forbiddenPage('You Must be admin');
}

if (!empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('Configuration disabled');
}

$page = new Page('Create plugin');

// Define the plugins directory
$pluginsDir = __DIR__ . '/plugins/';
?>
<div class="container">
    <?php
    // Check if the plugins directory is writable
    if (!is_writable($pluginsDir)) {
        ?>
        <div class="alert alert-danger">
            <strong>Warning!</strong> The <code><?php echo $pluginsDir; ?></code> folder is not writable.<br>
            This folder must be writable to make the plugin creation functionality work correctly.<br>
            <strong>Command to fix:</strong><br>
            <code>chmod -R 775 <?php echo $pluginsDir; ?></code><br>
            If necessary, you may also need to change the folder ownership.
        </div>
        <?php
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h2><i class="fas fa-plug"></i> Create New Plugin</h2>
            <p>
                <i class="fas fa-info-circle"></i>
                This tool will generate a base code structure for a new plugin based on the SQL you provide.
                This is not the final plugin code but serves as a starting point to help you kickstart your plugin development process.
                After creating this base, you can add custom logic and features as needed to complete your plugin.
            </p>
            <ul>
                <li><i class="fas fa-file-signature"></i> Provide a name for the plugin.</li>
                <li><i class="fas fa-database"></i> Optionally, enter the SQL code to create your plugin's table in the text area below.</li>
                <li><i class="fas fa-check-circle"></i> Click "<i class="fas fa-plus-circle"></i> Create Plugin" to submit the data.</li>
            </ul>
            <small>
                For more detailed instructions on how to further develop and customize your plugin, please refer to the
                <a href="https://github.com/WWBN/AVideo/wiki/How-to-code-a-AVideo-Platform-plugin" target="_blank">AVideo Plugin Development Guide</a> on GitHub.
            </small>

        </div>
        <div class="panel-body">
            <form id="createPluginForm">
                <div class="form-group">
                    <label for="pluginName"><i class="fas fa-tag"></i> Plugin Name</label>
                    <input type="text" class="form-control" id="pluginName" name="pluginName" placeholder="Enter Plugin Name" required>
                </div>
                <div class="form-group">
                    <label for="createTableSQL"><i class="fas fa-database"></i> CREATE TABLE SQL (Optional)</label>
                    <textarea class="form-control" id="createTableSQL" name="createTableSQL" rows="5" placeholder="Paste your CREATE TABLE SQL here (optional)"></textarea>
                </div>
                <button type="button" id="createPluginButton" class="btn btn-success btn-block"><i class="fas fa-plus-circle"></i> Create Plugin</button>
            </form>
            <div id="responseMessage" class="alert" style="display:none; margin-top: 10px;"></div>
        </div>

        <!-- Section for additional information -->
        <div class="panel-footer">
            <h3><i class="fas fa-folder-open"></i> Existing Plugin Zips</h3>
            <div id="zipFilesList">
                <!-- Zip files list will be loaded here -->
            </div>
        </div>
    </div>
</div>


<script>
    var listModal = getPleaseWait();
    var deleteModal = getPleaseWait();
    $(document).ready(function() {
        // Auto-format plugin name input
        $('#pluginName').on('input', function() {
            let value = $(this).val();
            value = value.replace(/[^a-zA-Z0-9_]/g, ''); // Remove any invalid character
            if (value.length > 0) {
                value = value.charAt(0).toUpperCase() + value.slice(1); // Capitalize first letter
            }
            $(this).val(value);
        });

        // AJAX to create plugin
        $('#createPluginButton').on('click', function() {
            let pluginName = $('#pluginName').val();
            let createTableSQL = $('#createTableSQL').val();

            if (pluginName) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'CreatePlugin/create.json.php',
                    type: 'POST',
                    data: {
                        pluginName,
                        createTableSQL
                    },
                    success: function(response) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            $('#responseMessage').removeClass('alert-success').addClass('alert-danger').text('Error: ' + response.msg).show();
                        } else {
                            let successMessage = '<i class="fas fa-check-circle"></i> Plugin created successfully!<br>' +
                                '<i class="fas fa-plug"></i> Plugin Name: ' + response.pluginName + '<br>' +
                                '<i class="fas fa-folder"></i> Plugin Directory: ' + response.pluginDir + '<br>' +
                                '<i class="fas fa-database"></i> Tables Found: ' + response.tables.join(', ') + '<br>' +
                                '<i class="fas fa-file-code"></i> Files Created: <ul>';
                            response.createdFiles.forEach(file => successMessage += '<li>' + file + '</li>');
                            successMessage += '</ul>';

                            $('#responseMessage').removeClass('alert-danger').addClass('alert-success').html(successMessage).show();
                            loadZipFiles();
                        }
                    },
                    error: function() {
                        modal.hidePleaseWait();
                        $('#responseMessage').removeClass('alert-success').addClass('alert-danger').text('An error occurred while creating the plugin. Please try again later.').show();
                    }
                });
            } else {
                $('#responseMessage').removeClass('alert-success').addClass('alert-danger').text('Please fill the name').show();
            }
        });

        // Function to load zip files
        function loadZipFiles() {
            listModal.showPleaseWait();
            $.getJSON(webSiteRootURL + 'CreatePlugin/list_zip_files.php', function(data) {
                listModal.hidePleaseWait();
                if (data.length === 0) {
                    $('#zipFilesList').html('<p><i class="fas fa-info-circle"></i> No zip files available.</p>');
                } else {
                    let listHtml = '<ul class="list-group">';
                    data.forEach(file => {
                        listHtml += '<li class="list-group-item">' +
                            ' <a href="' + webSiteRootURL + 'CreatePlugin/plugins/' + file + '" class="btn btn-sm btn-primary" download><i class="fas fa-download"></i> Download</a> ' +
                            ' <button class="btn btn-sm btn-danger" onclick="deleteZipFile(\'' + file + '\')"><i class="fas fa-trash-alt"></i> Delete</button> ' +
                            file +
                            '</li>';
                    });
                    listHtml += '</ul>';
                    $('#zipFilesList').html(listHtml);
                }
            });
        }

        // Function to delete zip file
        window.deleteZipFile = function(fileName) {
            deleteModal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'CreatePlugin/delete_zip_file.php',
                type: 'POST',
                data: {
                    fileName
                },
                success: function(response) {
                    deleteModal.hidePleaseWait();
                    if (response.error) {
                        alert('Error: ' + response.msg);
                    } else {
                        loadZipFiles(); // Refresh list
                    }
                }
            });
        };

        // Initial load of zip files
        loadZipFiles();
    });
</script>
<?php
$page->print();
?>
