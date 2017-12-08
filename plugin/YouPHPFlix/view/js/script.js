var video;
var cat;
var videos_id;

function loadPlayLists() {
    $.ajax({
        url: webSiteRootURL+'playLists.json',
        success: function (response) {
            $('#searchlist').html('');
            for (var i in response) {
                if (!response[i].id) {
                    continue;
                }
                console.log(response[i]);
                var icon = "lock"
                if (response[i].status == "public") {
                    icon = "globe"
                }

                var checked = "";
                for (var x in response[i].videos) {
                    if (response[i].videos[x].id == videos_id) {
                        checked = "checked";
                    }
                }

                $("#searchlist").append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>' + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault' + response[i].id + '" name="someSwitchOption' + response[i].id + '" class="playListsIds" type="checkbox" value="' + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault' + response[i].id + '" class="label-success"></label></div></a>');
            }
            $('#searchlist').btsListFilter('#searchinput', {itemChild: 'span'});
            $('.playListsIds').change(function () {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'playListAddVideo.json',
                    method: 'POST',
                    data: {
                        'videos_id': videos_id,
                        'add': $(this).is(":checked"),
                        'playlists_id': $(this).val()
                    },
                    success: function (response) {
                        console.log(response);
                        modal.hidePleaseWait();
                    }
                });
                return false;
            });
        }
    });
}

$(function () {

    $(".thumbsImage").on("mouseenter", function () {
        //$(this).find(".thumbsGIF").height($(this).find(".thumbsJPG").height());
        //$(this).find(".thumbsGIF").width($(this).find(".thumbsJPG").width());
        $(this).find(".thumbsGIF").stop(true, true).fadeIn();
    });

    $(".thumbsImage").on("mouseleave", function () {
        $(this).find(".thumbsGIF").stop(true, true).fadeOut();
    });


    $(".thumbsImage").on("click", function () {
        var img = $(this).find(".thumbsGIF").attr('src');
        videos_id = $(this).attr('videos_id');

        if (!img || true) {
            img = $(this).attr('poster');
        }
        var desc = $(this).find(".videoDescription").text();
        var title = $(this).find(".tile__title").text();

        var row = $(this).closest('.row');
        var poster = $(row).find('.poster');
        $(".arrow-down").fadeOut();

        $(".thumbsImage").removeClass('active');
        $(this).addClass('active');
        $(this).parent().find(".arrow-down").fadeIn('slow');

        $('.poster').not(poster).slideUp();

        $(row).find('.poster').slideDown();
        $(row).find('.footerBtn, .labelPoints').fadeIn();
        $(row).find('.poster').css({'background-image': 'url(' + img + ')'});
        $(row).find('.infoText, .infoTitle').fadeOut('slow', function () {
            $(row).find('.infoText').text(desc);
            $(row).find('.infoTitle').text(title);
            $(row).find('.infoText, .infoTitle').fadeIn('slow');
        });
        video = $(this).attr('video');
        cat = $(this).attr('cat');
        $('.playBtn').attr('src', webSiteRootURL+'cat/'+cat+'/video/'+video);
        loadPlayLists();
    });

    $('.carousel').flickity({
        lazyLoad: 7,
        setGallerySize: false,
        cellAlign: 'left'
    });

    $('.myList').webuiPopover({
        style: 'inverse',
        url: '#popover'
    });
    $('#addPlayList').click(function () {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'addNewPlayList',
            method: 'POST',
            data: {
                'videos_id': videos_id,
                'status': $('#publicPlayList').is(":checked") ? "public" : "private",
                'name': $('#playListName').val()
            },
            success: function (response) {
                if (response.status * 1 > 0) {
                    // update list
                    loadPlayLists();
                    $('#searchlist').btsListFilter('#searchinput', {itemChild: 'span'});
                    $('#playListName').val("");
                    $('#publicPlayList').prop('checked', true);
                }
                modal.hidePleaseWait();
            }
        });
        return false;
    });
    
    $(window).on('resize', function () {
        $('#ifvideo')
    });
});

