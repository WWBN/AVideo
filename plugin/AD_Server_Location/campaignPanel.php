<?php
$countries = IP2Location::getCountries();
?>
<div class="form-group col-sm-4">
    <label for="status"><?php echo __("Country"); ?>:</label>
    <select class="form-control input-sm" name="country" id="country">
        <option>All</option>
        <?php
        foreach ($countries as $key => $value) {
            echo '<option>' . $value . '</option>';
        }
        ?>
    </select>
</div> 
<div class="form-group col-sm-4">
    <label for="status"><?php echo __("Region"); ?>:</label>
    <select class="form-control input-sm" name="region" id="region">
        <option>All</option>
    </select>
</div> 
<div class="form-group col-sm-4">
    <label for="status"><?php echo __("City"); ?>:</label>
    <select class="form-control input-sm" name="city" id="city">
        <option>All</option>
    </select>
</div> 
<div class="form-group col-sm-12">
    <div class="btn btn-success btn-block" id="addLocation"><i class="fa fa-plus"></i> <?php echo __("Add Location Restriction"); ?></div>
</div> 
<div class="form-group col-sm-12">
    <ul class="list-group" id="locationList">
        
    </ul>
</div> 
<script type="text/javascript">
    
    function addLocation(country_name, region_name, city_name){
        $('#locationList').append('<li class="list-group-item">'+country_name+' - '+region_name+' - '+city_name+' <span  class="btn btn-danger  btn-xs btn-sm pull-right" onclick="$(this).parent().remove();"><i class="fa fa-trash"></i></span><input type="hidden" name="country_name[]" value="'+country_name+'"><input type="hidden" name="region_name[]" value="'+region_name+'"><input type="hidden" name="city_name[]" value="'+city_name+'"></li>');
    }
    
    $(document).ready(function () {
        
        $("#addLocation").on("click", function (e) {
            var country_name = $('#country').val();
            var region_name = $('#region').val();
            var city_name = $('#city').val();
            addLocation(country_name, region_name, city_name);
        });
        
        
        $("#country").on("change", function (e) {
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/User_Location/regions.json.php?country=' + $('#country').val(),
                success: function (response) {
                    $('#region').empty();
                    $('#region').append($("<option></option>").text('All'));
                    $('#city').empty();
                    $('#city').append($("<option></option>").text('All'));
                    $.each(response, function (key, value) {
                        $('#region').append($("<option></option>").attr("value", value).text(value));
                    });
                    modal.hidePleaseWait();
                }
            });
        });

        $("#region").on("change", function (e) {
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/User_Location/cities.json.php?country=' + $('#country').val() + '&region=' + $('#region').val(),
                success: function (response) {
                    $('#city').empty();
                    $('#city').append($("<option></option>").text('All'));
                    $.each(response, function (key, value) {
                        $('#city').append($("<option></option>").attr("value", value).text(value));
                    });
                    modal.hidePleaseWait();
                }
            });
        });

    });
</script>