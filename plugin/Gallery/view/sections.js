
$(function () {
    $("#sortable").sortable({
        stop: function (event, ui) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/Gallery/view/saveSort.json.php',
                method: 'POST',
                data: {
                    'sections': $("#sortable").sortable("toArray"),
                    'globalToken': $('#galleryToken').val()
                },
                success: function (response) {
                    modal.hidePleaseWait();
                    if (response.error) {
                        avideoAlertError(response.msg);
                    }
                }
            });
        }
    });
    $('.sectionsCheckbox').change(function () {
        modal.showPleaseWait();
        var name = $(this).attr("name");
        var isChecked = $(this).is(':checked');
        $.ajax({
            url: webSiteRootURL + 'plugin/Gallery/view/saveSort.json.php',
            method: 'POST',
            data: {
                'name': name,
                'isChecked': isChecked,
                'globalToken': $('#galleryToken').val()
            },
            success: function (response) {
                modal.hidePleaseWait();
                if (response.error) {
                    avideoAlertError(response.msg);
                }
            }
        });
    });
    $("#sortable").disableSelection();
});
