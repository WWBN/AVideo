<div style="display: none;" id="playListHolder">
    <div id="playListFilters">
        <?php
        if (!empty($collectionsList)) {
            ?>
            <select class="form-control" id="subPlaylistsCollection" >
                <option value="0"> <?php echo __("Show all"); ?></option>
                <?php
                foreach ($collectionsList as $value) {
                    echo '<option value="' . $value['serie_playlists_id'] . '">' . $value['title'] . '</option>';
                }
                ?>
            </select>
            <?php
        }
        ?>
        <input type="search" id="playListSearch" class="form-control" placeholder=" <?php echo __("Search"); ?>"/>
        <select class="form-control" id="embededSortBy" >
            <option value="default"> <?php echo __("Sort"); ?></option>
            <option value="titleAZ" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Title (A-Z)"); ?></option>
            <option value="titleZA" data-icon="glyphicon-sort-by-attributes-alt"> <?php echo __("Title (Z-A)"); ?></option>
            <option value="newest" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Date added (newest)"); ?></option>
            <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt" > <?php echo __("Date added (oldest)"); ?></option>
            <option value="popular" data-icon="glyphicon-thumbs-up"> <?php echo __("Most popular"); ?></option>
            <?php
            if (empty($advancedCustom->doNotDisplayViews)) {
                ?> 
                <option value="views_count" data-icon="glyphicon-eye-open"  <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="vjs-playlist" style="" id="playList">
        <!--
          The contents of this element will be filled based on the
          currently loaded playlist
        -->
    </div>
</div>