<div class="tabbable-line">
    <ul class="nav nav-tabs">
        <li class="active" >
            <a data-toggle="tab" href="#tabStreamMetaData"><i class="fas fa-key"></i> <?php echo __("Stream Meta Data"); ?></a></li>
        <li class="">
            <a data-toggle="tab" href="#tabPosterImage"><i class="fas fa-images"></i> <?php echo __("Poster Image"); ?></a></li>
        <li class="" >
            <a data-toggle="tab" href="#tabUserGroups"><i class="fas fa-users"></i> <?php echo __("User Groups"); ?></a></li>

    </ul>
    <div class="tab-content">
        <div id="tabStreamMetaData" class="tab-pane fade in active">

            <div class="panel panel-default">
                <div class="panel-heading"><i class="fas fa-cog"></i> <?php echo __("Stream Settings"); ?></div>
                <div class="panel-body"> 
                    <div class="row">
                        <div class="col-sm-6">

                            <div class="form-group">
                                <label for="title"><?php echo __("Title"); ?>:</label>
                                <input type="text" class="form-control" id="title" value="<?php echo $trasnmition['title'] ?>">
                            </div>  
                            <div class="form-group">
                                <label for="title"><?php echo __("Category"); ?>:</label>
                                <?php
                                echo Layout::getCategorySelect('categories_id', $trasnmition['categories_id']);
                                ?>
                            </div>  
                            <div class="form-group">
                                <span class="fa fa-globe"></span> <?php echo __("Make Stream Publicly Listed"); ?> 
                                <div class="material-switch pull-right">
                                    <input id="listed" type="checkbox" value="1" <?php echo!empty($trasnmition['public']) ? "checked" : ""; ?> onchange="saveStream();"/>
                                    <label for="listed" class="label-success"></label> 
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">

                            <div class="form-group">
                                <label for="description"><?php echo __("Description"); ?>:</label>
                                <textarea rows="6" class="form-control" id="description"><?php echo $trasnmition['description'] ?></textarea>
                            </div>
                        </div>
                    </div>  
                </div>
                <div class="panel-footer">
                    <button type="button" class="btn btn-success btnSaveStream" id="btnSaveStream"><?php echo __("Save Stream"); ?></button>
                </div>
            </div>
        </div>
        <div id="tabPosterImage" class="tab-pane fade"> 

            <div class="panel panel-default ">
                <div class="panel-heading">
                    <?php
                    echo __("Upload Poster Image");
                    ?>
                    <button class="btn btn-danger btn-sm btn-xs pull-right" id="removePoster">
                        <i class="far fa-trash-alt"></i> <?php echo __("Remove Poster"); ?>
                    </button>
                </div>
                <div class="panel-body"> 
                    <input id="input-jpg" type="file" class="file-loading" accept="image/*">
                </div>
            </div>
        </div>
        <div id="tabUserGroups" class="tab-pane fade"> 

            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __("Groups That Can See This Stream"); ?><br><small><?php echo __("Uncheck all to make it public"); ?></small></div>
                <div class="panel-body"> 
                    <?php
                    $ug = UserGroups::getAllUsersGroups();
                    foreach ($ug as $value) {
                        ?>
                        <div class="form-group">
                            <span class="fa fa-users"></span> <?php echo $value['group_name']; ?>
                            <div class="material-switch pull-right">
                                <input id="group<?php echo $value['id']; ?>" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroups" <?php echo (in_array($value['id'], $groups) ? "checked" : "") ?>/>
                                <label for="group<?php echo $value['id']; ?>" class="label-success"></label>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="panel-footer">
                    <button type="button" class="btn btn-success btnSaveStream" id="btnSaveStream"><?php echo __("Save Stream"); ?></button>
                    <a href="<?php echo $global['webSiteRootURL']; ?>usersGroups" class="btn btn-primary"><span class="fa fa-users"></span> <?php echo __("Add more user Groups"); ?></a>
                </div>
            </div>
        </div>
    </div> 
</div>  



