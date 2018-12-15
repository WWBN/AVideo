<?php
if(!empty($advancedCustomUser->disablePersonalInfo)){
    return false;
}
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';
$text = "-- " . __('Select one Option') . " --";
$myCountry = $user->getCountry();
$myRegion = $user->getRegion();
$myCity = $user->getCity();
?>
<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("First Name"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <input name="first_name" id="first_name" placeholder="<?php echo __("Last Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getFirst_name(); ?>" >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Last Name"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <input name="last_name"  id="last_name" placeholder="<?php echo __("Last Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getLast_name(); ?>"  >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Address"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <input name="address"  id="address" placeholder="<?php echo __("Address"); ?>" class="form-control"  type="text" value="<?php echo $user->getAddress(); ?>"  >
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label"><?php echo __("Zip Code"); ?></label>
    <div class="col-md-8 inputGroupContainer">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <input name="zip_code"  id="zip_code" placeholder="<?php echo __("Zip Code"); ?>" class="form-control"  type="text" value="<?php echo $user->getZip_code(); ?>"  >
        </div>
    </div>
</div>

<?php
$countries = IP2Location::getCountries();
?>
<div class="form-group">
    <label for="status" class="col-md-4 control-label"><?php echo __("Country"); ?>:</label>
    <div class="col-md-8 inputGroupContainer">

        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <select class="form-control input-sm" name="country" id="country">
                <option><?php echo $text; ?></option>
                <?php
                foreach ($countries as $key => $value) {
                    $selected = "";
                    if ($myCountry === $value) {
                        $selected = 'selected';
                    }
                    echo '<option ' . $selected . '>' . $value . '</option>';
                }
                ?>
            </select>
        </div>

    </div>
</div> 


<div class="form-group">
    <label for="status" class="col-md-4 control-label"><?php echo __("Region"); ?>:</label>
    <div class="col-md-8 inputGroupContainer">

        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <select class="form-control input-sm" name="region" id="region">
                <option><?php echo $text; ?></option>
            </select>
        </div>

    </div>
</div> 

<div class="form-group">
    <label for="status" class="col-md-4 control-label"><?php echo __("City"); ?>:</label>
    <div class="col-md-8 inputGroupContainer">

        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
            <select class="form-control input-sm" name="city" id="city">
                <option><?php echo $text; ?></option>
            </select>
        </div>

    </div>
</div> 

<div class="form-group">
    <div class="col-md-12 ">
        <div id="documentImage"></div>
        <center>
            <a id="upload-btnDocument" class="btn btn-success"><i class="fa fa-upload"></i> <?php echo __("Upload a Document Image"); ?></a>
        </center>
    </div>
    <input type="file" name="uploadDocument" id="uploadDocument" value="Choose a file" accept="image/*" style="display: none;" />
</div>
<script>

    var uploadCropDocument;
    function savePersonalInfo() {
        $('#aPersonalInfo').tab('show');
         setTimeout(function(){savePersonalInfoAjax(); }, 1000);

    }
    
    function savePersonalInfoAjax(){
        modal.showPleaseWait();

        uploadCropDocument.croppie('result', {
            type: 'canvas',
            size: 'viewport'
        }).then(function (resp) {
            $.ajax({
                type: "POST",
                url: "<?php echo $global['webSiteRootURL']; ?>objects/userUpdatePersonal.json.php",
                data: {
                    imgBase64: resp,
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    address: $('#address').val(),
                    zip_code: $('#zip_code').val(),
                    country: $('#country').val(),
                    region: $('#region').val(),
                    city: $('#city').val()
                }
            }).done(function (o) {
                $('#aBasicInfo').tab('show');
                modal.hidePleaseWait();
            });
        });
    }



    $(document).ready(function () {
        $('#uploadDocument').on('change', function () {
            readFile(this, uploadCropDocument);
        });
        $('#upload-btnDocument').on('click', function (ev) {
            $('#uploadDocument').trigger("click");
        });


        uploadCropDocument = $('#documentImage').croppie({
            url: '<?php echo $global['webSiteRootURL']; ?>objects/userDocument.png.php?users_id=<?php echo User::getId(); ?>',
                        enableExif: true,
                        enforceBoundary: false,
                        mouseWheelZoom: false,
                        viewport: {
                            width: 640,
                            height: 450
                        },
                        boundary: {
                            width: 640,
                            height: 450
                        }
                    });

                    $("#country").on("change", function (e) {
                        //modal.showPleaseWait();
                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/User_Location/regions.json.php?country=' + $('#country').val(),
                            success: function (response) {
                                $('#region').empty();
                                $('#region').append($("<option></option>").text('<?php echo $text; ?>'));
                                $('#city').empty();
                                $('#city').append($("<option></option>").text('<?php echo $text; ?>'));
                                var found = false;
                                $.each(response, function (key, value) {
                                    var selected = '';
                                    if (value === '<?php echo $myRegion; ?>') {
                                        selected = 'selected';
                                        found = true;
                                    }
                                    $('#region').append($("<option " + selected + "></option>").attr("value", value).text(value));
                                });
                                //modal.hidePleaseWait();
                                if (found) {
                                    $("#region").trigger('change');
                                }
                            }
                        });
                    });

                    $("#region").on("change", function (e) {
                        //modal.showPleaseWait();
                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/User_Location/cities.json.php?country=' + $('#country').val() + '&region=' + $('#region').val(),
                            success: function (response) {
                                $('#city').empty();
                                $('#city').append($("<option></option>").text('<?php echo $text; ?>'));
                                $.each(response, function (key, value) {
                                    var selected = '';
                                    if (value === '<?php echo $myCity; ?>') {
                                        selected = 'selected';
                                    }
                                    $('#city').append($("<option " + selected + "></option>").attr("value", value).text(value));
                                });
                                //modal.hidePleaseWait();
                            }
                        });
                    });

<?php
if (!empty($myCountry)) {
    ?>
                        setTimeout(function () {
                            $("#country").trigger('change');
                        }, 1000);
    <?php
}
?>

                });
</script>