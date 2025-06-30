var currentFontsize = 100;
var connectionMenuJustDrag = false;

$(function () {
    // Enable dragging of the toolbar toggle button
    $("#connectionMenu-toolbar-toggle").draggable({
        axis: "y",
        containment: 'window',
        scroll: false,
        start: function () {
            connectionMenuJustDrag = true;
        },
        stop: function () {
            $("#connectionMenu-toolbar-toggle").css("left", "");
            setCookie('connectionMenu-toolbar-toggle-top', $("#connectionMenu-toolbar-toggle").position().top, 30);
            setTimeout(function () {
                connectionMenuJustDrag = false;
            }, 200);
        }
    });

    setConnectionMenuTop();

    loadConnectionsList();

    adjustConnectionMenuPosition();
    setTimeout(function () {
        adjustConnectionMenuPosition();
    }, 1000);
    $(window).resize(function () {
        if (!connectionMenuJustDrag) {
            adjustConnectionMenuPosition();
        }
    });
    $(window).scroll(function () {
        if (!connectionMenuJustDrag) {
            adjustConnectionMenuPosition();
        }
    });

    // Close connection menu when clicking outside of it
    $(document).on('click', function (event) {
        if (!$(event.target).closest('#connectionMenu-toolbar, #connectionMenu-toolbar-toggle').length) {
            $('#connectionMenu-toolbar').removeClass('active');
        }
    });
});

function toggleConnectionMenu() {
    if (connectionMenuJustDrag) {
        return false;
    }
    $('#connectionMenu-toolbar').toggleClass('active');
    if ($('#connectionMenu-toolbar').hasClass('active')) {
        $('#connectionMenu-toolbar-overlay').show();
    } else {
        setTimeout(() => {
            $('#connectionMenu-toolbar-overlay').fadeOut();
        }, 600);
    }
}

function setConnectionMenuTop() {
    if (typeof getCookie !== 'function') {
        setTimeout(function () {
            setConnectionMenuTop();
        }, 500);
        return false;
    }

    var connectionMenuTop = getCookie('connectionMenu-toolbar-toggle-top');
    if (!empty(connectionMenuTop)) {
        console.log('setConnectionMenuTop', connectionMenuTop);
        $("#connectionMenu-toolbar-toggle").css("top", connectionMenuTop + 'px');
    }
    $("#connectionMenu-toolbar-toggle").show();

}

function adjustConnectionMenuPosition() {
    const divVideo = $('#mvideo');
    if (divVideo.length > 0) {
        const divConTop = $('#connectionMenu-toolbar-toggle').offset().top;
        const bottom = divVideo.offset().top + divVideo.outerHeight();

        if (divConTop < bottom && divConTop > bottom - 100) {
            $("#connectionMenu-toolbar-toggle").css("top", (bottom + 10) + 'px');
        }
    }
}

// Function to load connections list via AJAX
function loadConnectionsList() {
    $.ajax({
        url: webSiteRootURL + 'plugin/UserConnections/myConnections.json.php', // URL to fetch connections
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Check if there are connections available
            if (response && response.data && response.data.length > 0) {
                var connectionsList = response.data;
                var $connectionList = $('#connectionList');
                $connectionList.empty(); // Clear existing list items

                // Iterate through the connections and create list items
                $.each(connectionsList, function (index, connection) {
                    var listItem = `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="btn-group btn-group-justified" role="group">
                                <a href="${connection.channelLink}" class="btn btn-primary btn-xs">
                                    <img src="${webSiteRootURL}user/${connection.friend_users_id}/foto.png" class="img img-responsive img-rounded pull-left" alt="User Photo">
                                    <strong>${connection.friend}</strong>
                                </a>
                                ${connection.chatButton}
                                ${connection.callButton}
                            </div>
                        </li>`;
                    $connectionList.append(listItem);
                });
            } else {
                // No connections found
                $('#connectionList').html('<li class="list-group-item text-center">No connections available</li>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching connections:', error);
            $('#connectionList').html('<li class="list-group-item text-center text-danger">Failed to load connections</li>');
        }
    });
}
