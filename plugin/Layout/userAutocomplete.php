<?php
if (!empty($default_users_id)) {
    $u = new User($default_users_id);
    if (!empty($u->getUser())) {
        $name = $u->getUser();
        $user_image = $u->getPhotoDB();
    }
}

if (empty($name)) {
    $name = '';
    $user_image = getURL('view/img/userSilhouette.jpg');
    $default_users_id = 0;
}

?>
<div class="col-md-2">
    <img id="user-img<?php echo $id; ?>" src="<?php echo $user_image; ?>" class="img img-responsive img-circle" style="max-height: 60px;" alt="User Photo">
</div>
<div class="col-md-10">
    <input id="user<?php echo $id; ?>" placeholder="<?php echo __("User"); ?>" class="form-control" name="user<?php echo $id; ?>" value="<?php echo $name; ?>">
    <input type="hidden" id="<?php echo $id; ?>" value="<?php echo $default_users_id; ?>" name="<?php echo $id; ?>">
</div>
<script>
    function updateUserAutocomplete<?php echo $id; ?>() {
        var data = <?php echo _json_encode((object) $parameters); ?>;
        data.users_id = $('#<?php echo $id; ?>').val();
        var users_id = data.users_id;
        console.log('updateUserAutocomplete<?php echo $id; ?>', data.users_id );
        resetUserAutocomplete<?php echo $id; ?>();
        if (data.users_id && data.users_id !== '0') {
            if(typeof modal === 'object'){
                modal.showPleaseWait();
            }
            $.ajax({
                url: webSiteRootURL + 'objects/users.json.php',
                type: "POST",
                data: data,
                success: function (data) {
                    if (data.rows && data.rows[0]) {
                        $("#user<?php echo $id; ?>").val(data.rows[0].identification);
                        $("#<?php echo $id; ?>").val(users_id);
                        var photoURL = data.rows[0].photo
                        $("#user-img<?php echo $id; ?>").attr("src", photoURL);
                    }
                    if(typeof modal === 'object'){
                        modal.hidePleaseWait();
                    }
                }
            });
        }
    }

    function resetUserAutocomplete<?php echo $id; ?>() {
        $("#user<?php echo $id; ?>").val('');
        $("#<?php echo $id; ?>").val(0);
        var photoURL = webSiteRootURL + 'img/userSilhouette.jpg'
        $("#user-img<?php echo $id; ?>").attr("src", photoURL);
    }

    $(document).ready(function () {


        $("#<?php echo $id; ?>").change(function () {
            updateUserAutocomplete<?php echo $id; ?>();
        });

        $("#<?php echo $id; ?>").bind("change", function () {
            updateUserAutocomplete<?php echo $id; ?>();
        });

        $("#user<?php echo $id; ?>").autocomplete({
            minLength: 0,
            source: function (req, res) {
                var data = <?php echo _json_encode((object) $parameters); ?>;
                data.searchPhrase = req.term;
                $.ajax({
                    url: webSiteRootURL + 'objects/users.json.php',
                    type: "POST",
                    data: data,
                    success: function (data) {
                        res(data.rows);
                    }
                });
            },
            focus: function (event, ui) {
                $("#user<?php echo $id; ?>").val(ui.item.identification);
                return false;
            },
            select: function (event, ui) {
                $("#user<?php echo $id; ?>").val(ui.item.identification);
                $("#<?php echo $id; ?>").val(ui.item.id);
                var photoURL = webSiteRootURL + 'img/userSilhouette.jpg'
                if (ui.item.photo) {
                    photoURL = ui.item.photo;
                }
                $("#user-img<?php echo $id; ?>").attr("src", photoURL);
                return false;
            }
        }).autocomplete("instance")._renderItem = function (ul, item) {
            return $("<li>").append("<div>["+item.id+"] " + item.creator + "</div>").appendTo(ul);
        };
    });
</script>