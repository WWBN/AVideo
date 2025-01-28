
<div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-user"></i> <?php echo __("Active Lives"); ?> </div>
    <div class="panel-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Key</th>
                    <th>Users ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rows = LiveTransmitionHistory::getActiveLives();
                foreach ($rows as $item) {
                    $users_id_live = $item['users_id'];
                    ?>
                    <tr>
                        <td><?php echo $item['title'] ?></td>
                        <td><?php echo $item['key'] ?></td>
                        <td>
                            <img src="<?php echo User::getPhoto($users_id_live); ?>" class="img img-thumbnail img-responsive pull-left" style="max-height: 100px; margin: 0 10px;" alt="User Photo" />
                            <a href="<?php echo User::getChannelLink($users_id_live); ?>" class="btn btn-default">
                                <i class="fas fa-play-circle"></i>
                                <?php echo User::getNameIdentificationById($users_id_live); ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</div>
