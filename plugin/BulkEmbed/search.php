<?php
require_once '../../videos/configuration.php';
if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$obj = YouPHPTubePlugin::getObjectData("BulkEmbed");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Bulk Embed</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            #custom-search-input{
                padding: 3px;
                border: solid 1px #E4E4E4;
                border-radius: 6px;
                background-color: #fff;
            }

            #custom-search-input input{
                border: 0;
                box-shadow: none;
            }

            #custom-search-input button{
                margin: 2px 0 0 0;
                background: none;
                box-shadow: none;
                border: 0;
                color: #666666;
                padding: 0 8px 0 10px;
                border-left: solid 1px #ccc;
            }

            #custom-search-input button:hover{
                border: 0;
                box-shadow: none;
                border-left: solid 1px #ccc;
            }

            #custom-search-input .glyphicon-search{
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
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <section>
                <form id="search-form" name="search-form" onsubmit="return search()">
                    <div id="custom-search-input">
                        <div class="input-group col-md-12">
                            <input type="search" id="query" class="form-control input-lg" placeholder="Search YouTube" />
                            <span class="input-group-btn">
                                <button class="btn btn-info btn-lg" type="button">
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
                <br>
                <ul id="results"></ul>
                <div id="buttons"></div>
            </section>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>

            var gapikey = '<?php echo $obj->API_KEY; ?>';

            $(function () {
                $('#search-form').submit(function (e) {
                    e.preventDefault();
                });

                $('#getAll').click(function () {
                    var videoLink = new Array();
                    $("input:checkbox[name=videoCheckbox]").each(function () {
                        videoLink.push($(this).val());
                    });
                    console.log(videoLink);
                    saveIt(videoLink);
                });
                $('#getSelected').click(function () {
                    var videoLink = new Array();
                    $("input:checkbox[name=videoCheckbox]:checked").each(function () {
                        videoLink.push($(this).val());
                    });
                    console.log(videoLink);
                    saveIt(videoLink);
                });
            });

            function saveIt(videoLink) {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/BulkEmbed/save.json.php',
                    data: {"videoLink": videoLink},
                    type: 'post',
                    success: function (response) {
                        if (!response.error) {
                            swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your videos have been saved!"); ?>", "success");
                        } else {
                            swal("<?php echo __("Sorry!"); ?>", response.msg.join("<br>"), "error");
                        }
                        modal.hidePleaseWait();
                    }
                });
            }

            function search() {
                // clear 
                $('#results').html('');
                $('#buttons').html('');

                // get form input
                q = $('#query').val();  // this probably shouldn't be created as a global

                // run get request on API
                $.get(
                        "https://www.googleapis.com/youtube/v3/search", {
                            part: 'snippet, id',
                            q: q,
                            type: 'video',
                            key: gapikey,
                            maxResults: 50
                        }, function (data) {
                    var nextPageToken = data.nextPageToken;
                    var prevPageToken = data.prevPageToken;

                    // Log data
                    console.log(data);

                    $.each(data.items, function (i, item) {

                        // Get Output
                        var output = getOutput(item);

                        // display results
                        $('#results').append(output);
                    });

                    var buttons = getButtons(prevPageToken, nextPageToken);

                    // Display buttons
                    $('#buttons').append(buttons);
                });
            }

// Next page function
            function nextPage() {
                var token = $('#next-button').data('token');
                var q = $('#next-button').data('query');


                // clear 
                $('#results').html('');
                $('#buttons').html('');

                // get form input
                q = $('#query').val();  // this probably shouldn't be created as a global

                // run get request on API
                $.get(
                        "https://www.googleapis.com/youtube/v3/search", {
                            part: 'snippet, id',
                            q: q,
                            pageToken: token,
                            type: 'video',
                            key: gapikey,
                            maxResults: 50
                        }, function (data) {

                    var nextPageToken = data.nextPageToken;
                    var prevPageToken = data.prevPageToken;

                    // Log data
                    console.log(data);

                    $.each(data.items, function (i, item) {

                        // Get Output
                        var output = getOutput(item);

                        // display results
                        $('#results').append(output);
                    });

                    var buttons = getButtons(prevPageToken, nextPageToken);

                    // Display buttons
                    $('#buttons').append(buttons);
                });
            }

// Previous page function
            function prevPage() {
                var token = $('#prev-button').data('token');
                var q = $('#prev-button').data('query');


                // clear 
                $('#results').html('');
                $('#buttons').html('');

                // get form input
                q = $('#query').val();  // this probably shouldn't be created as a global

                // run get request on API
                $.get(
                        "https://www.googleapis.com/youtube/v3/search", {
                            part: 'snippet, id',
                            q: q,
                            pageToken: token,
                            type: 'video',
                            key: gapikey,
                            maxResults: 50
                        }, function (data) {

                    var nextPageToken = data.nextPageToken;
                    var prevPageToken = data.prevPageToken;

                    // Log data
                    console.log(data);

                    $.each(data.items, function (i, item) {

                        // Get Output
                        var output = getOutput(item);

                        // display results
                        $('#results').append(output);
                    });

                    var buttons = getButtons(prevPageToken, nextPageToken);

                    // Display buttons
                    $('#buttons').append(buttons);
                });
            }

// Build output
            function getOutput(item) {
                var videoID = item.id.videoId;
                var title = item.snippet.title;
                var description = item.snippet.description;
                var thumb = item.snippet.thumbnails.high.url;
                var channelTitle = item.snippet.channelTitle;
                var videoDate = item.snippet.publishedAt;

                // Build output string
                var output = '<li>' +
                        '<div class="list-left">' +
                        '<img src="' + thumb + '">' +
                        '</div>' +
                        '<div class="list-right">' +
                        '<h3><input type="checkbox" value="https://youtube.com/embed/' + videoID + '" name="videoCheckbox"><a target="_blank" href="https://youtube.com/embed/' + videoID + '?rel=0">' + title + '</a></h3>' +
                        '<small>By <span class="cTitle">' + channelTitle + '</span> on ' + videoDate + '</small>' +
                        '<p>' + description + '</p>' +
                        '</div>' +
                        '</li>' +
                        '<div class="clearfix"></div>' +
                        '';
                return output;
            }

            function getButtons(prevPageToken, nextPageToken) {
                if (!prevPageToken) {
                    var btnoutput = '<div class="button-container">' +
                            '<button id="next-button" class="paging-button" data-token="' + nextPageToken + '" data-query="' + q + '"' +
                            'onclick = "nextPage();">Next Page</button>' +
                            '</div>';
                } else {
                    var btnoutput = '<div class="button-container">' +
                            '<button id="prev-button" class="paging-button" data-token="' + prevPageToken + '" data-query="' + q + '"' +
                            'onclick = "prevPage();">Prev Page</button>' +
                            '<button id="next-button" class="paging-button" data-token="' + nextPageToken + '" data-query="' + q + '"' +
                            'onclick = "nextPage();">Next Page</button>' +
                            '</div>';
                }

                return btnoutput;
            }
        </script>
    </body>
</html>
