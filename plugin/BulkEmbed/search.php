<?php
require_once '../../videos/configuration.php';

if(!BulkEmbed::canBulkEmbed()){
    forbiddenPage('You cannot do this');
}

$obj = AVideoPlugin::getObjectData("BulkEmbed");

$_page = new Page(array('Search'));
?>
<style>
    #custom-search-input {
        padding: 3px;
        border: solid 1px #E4E4E4;
        border-radius: 6px;
        background-color: #fff;
    }

    #custom-search-input input {
        border: 0;
        box-shadow: none;
    }

    #custom-search-input button {
        margin: 2px 0 0 0;
        background: none;
        box-shadow: none;
        border: 0;
        color: #666666;
        padding: 0 8px 0 10px;
        border-left: solid 1px #ccc;
    }

    #custom-search-input button:hover {
        border: 0;
        box-shadow: none;
        border-left: solid 1px #ccc;
    }

    #custom-search-input .glyphicon-search {
        font-size: 23px;
    }

    #results li {
        padding: 10px 0;
        border-bottom: 1px dotted #ccc;
        list-style: none;
        overflow: auto;
    }

    .list-left {
        float: left;
        width: 20%;
    }

    .list-left img {
        width: 100%;
        padding: 3px;
        border: 1px solid #ccc;
    }

    .list-right {
        float: right;
        width: 78%;
    }

    .list-right h3 {
        margin: 0;
    }

    .list-right p {
        margin: 0;
    }

    .cTitle {
        color: #dd2826;
    }

    .button-container {
        margin-top: 25px;

    }

    .paging-button {
        background: #f4f4f4;
        padding: 0 13px;
        border: #ccc 1px solid;
        border-radius: 5px;
        color: #333;
        margin: 10px;
        cursor: pointer;
    }
</style>
<div class="container">

    <div class="panel panel-default">
        <div class="panel-heading">
            <form id="search-form" name="search-form">
                <div id="custom-search-input">
                    <div class="input-group col-md-12">
                        <input type="search" id="query" class="form-control input-lg" placeholder="Search YouTube / PlayList URL" />
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-lg" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </form>
            <br>
            <div class="row">
                <div class="col-sm-6">
                    <button class="btn btn-info btn-block" id="getAll"><?php echo __('Embed All'); ?></button>
                </div>
                <div class="col-sm-6">
                    <button class="btn btn-success btn-block" id="getSelected"><?php echo __('Embed Selected'); ?></button>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <ul id="results"></ul>
            <div id="buttons"></div>
        </div>
    </div>
</div>
<script>
    var playListName = '';

    var searchResults = {}; // Global variable to store the search results

    $(function() {
        $('#search-form').submit(function(e) {
            e.preventDefault();
            search(); // Call the search function
        });

        $('#getAll').click(function() {
            var videoLink = [];
            $("input:checkbox[name=videoCheckbox]").each(function() {
                videoLink.push($(this).val());
            });
            saveIt(videoLink);
        });

        $('#getSelected').click(function() {
            var videoLink = [];
            $("input:checkbox[name=videoCheckbox]:checked").each(function() {
                videoLink.push($(this).val());
            });
            saveIt(videoLink);
        });
    });

    function validURL(str) {
        var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
            '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
        return !!pattern.test(str);
    }

    function getFromUrl(url) {
        if (!validURL(url)) {
            return false;
        }

        var regex = /[?&]([^=#]+)=([^&#]*)/g,
            params = {},
            match;
        while (match = regex.exec(url)) {
            params[match[1]] = match[2];
        }
        return params;
    }

    function getPlayListId(url) {
        var result = getFromUrl(url);
        if (result && typeof result.list !== 'undefined') {
            return result.list;
        }
        return false;
    }

    // Function to handle search and store results globally
    function search(pageToken = '') {
        $('#results').html('');
        $('#buttons').html('');

        var query = $('#query').val();

        $.ajax({
            url: webSiteRootURL + 'plugin/BulkEmbed/search.json.php',
            type: 'POST',
            data: {
                query: query,
                pageToken: pageToken
            },
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    processData(response.data);
                    // Store search results globally
                    searchResults = response.data.items.reduce(function(map, item) {
                        map[item.link] = item;
                        return map;
                    }, {});
                }
            },
            error: function(xhr, status, error) {
                console.log("An error occurred: " + error);
            }
        });
    }

    function processData(data) {
        // Check if items array exists
        if (!data || !data.items || !Array.isArray(data.items)) {
            console.error('Invalid data format received from the server:', data);
            avideoAlertError("Invalid data format received from the server");
            return;
        }

        // Clear previous data
        $('#results').html('');
        $('#buttons').html('');

        // Process each item
        $.each(data.items, function(i, item) {
            var output = getOutput(item);
            $('#results').append(output);
        });

        // Handle pagination buttons
        var buttons = getButtons(data.prevPageToken, data.nextPageToken);
        $('#buttons').append(buttons);
    }


    function nextPage(token) {
        var query = $('#query').val();
        search(token);
    }

    function prevPage(token) {
        var query = $('#query').val();
        search(token);
    }

    function getOutput(item) {
        var output = '<li>' +
            '<div class="list-left">';

        if (item.isEmbedded) {
            // Apply grayscale effect to the image for embedded videos
            output += '<img src="' + item.thumbs + '" style="filter: grayscale(100%);">';
        } else {
            // Regular image for non-embedded videos
            output += '<img src="' + item.thumbs + '">';
        }

        output += '</div>' +
            '<div class="list-right">' +
            '<h3>';

        if (item.isEmbedded) {
            // If the video is embedded, show only the title and indicate it's already embedded
            output += '<i class="fa-regular fa-square-check"></i> <a target="_blank" href="' + item.link + '" target="_blank">' + item.title + '</a>';
            output += '<p><a class="btn btn-danger" href="' + webSiteRootURL + 'video/' + item.embeddedVideos_Id + '">' + __('Already embedded') + '</strong></a>';
        } else {
            // If the video is not embedded, include a checkbox for embedding
            output += '<input type="checkbox" value="' + item.link + '" name="videoCheckbox"> ';
            output += '<a target="_blank" href="' + item.link + '">' + item.title + '</a>';
        }

        output += '</h3>' +
            '<small>Published on ' + item.date + '</small>' +
            '<p>' + item.description + '</p>' +
            '</div>' +
            '<div class="clearfix"></div>' +
            '</li>';

        return output;
    }


    function getButtons(prevPageToken, nextPageToken) {
        var buttons = '';

        if (prevPageToken) {
            buttons += '<div class="button-container">' +
                '<button id="prev-button" class="paging-button" data-token="' + prevPageToken + '" onclick="prevPage(\'' + prevPageToken + '\')">Prev Page</button>' +
                '</div>';
        }

        if (nextPageToken) {
            buttons += '<div class="button-container">' +
                '<button id="next-button" class="paging-button" data-token="' + nextPageToken + '" onclick="nextPage(\'' + nextPageToken + '\')">Next Page</button>' +
                '</div>';
        }

        return buttons;
    }


    // Function to save selected videos
    function saveIt(videoLink) {
        modal.showPleaseWait();

        // Collect the video details for the selected videos
        var itemsToSave = [];
        $.each(videoLink, function(index, link) {
            if (searchResults[link]) {
                itemsToSave.push(searchResults[link]); // Push the video details from the search results
            }
        });

        // Send the data to save.json.php
        $.ajax({
            url: webSiteRootURL + 'plugin/BulkEmbed/save.json.php',
            type: 'POST',
            data: {
                "itemsToSave": itemsToSave,
                playListName: playListName
            },
            success: function(response) {
                if (!response.error) {
                    avideoAlertSuccess(__("Your videos have been saved!"));
                } else {
                    avideoAlertError(response.msg.join("<br>"));
                }
                modal.hidePleaseWait();
            }
        });
    }
</script>

<?php
$_page->print();
?>