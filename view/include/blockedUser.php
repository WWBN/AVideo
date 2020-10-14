 <!-- blocked user -->
<div class="row main-video" id="mvideo">
    <div class="col-md-2 firstC"></div>
    <div class="col-md-8 secC">
        <div id="videoContainer">
            <div class="panel panel-default">
                <div class="panel-body ">
                    <center>
                        <br>
                        <br>
                        <i class="fas fa-user-slash fa-3x"></i><hr>
                        You've blocked user (<?php echo User::getNameIdentificationById($video['users_id'])?>)<br>
                        You won't see any comments or videos from this user<hr>
                        <?php
                        echo User::getblockUserButton($video['users_id']);
                        ?>
                        <br>
                        <br>
                    </center>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
