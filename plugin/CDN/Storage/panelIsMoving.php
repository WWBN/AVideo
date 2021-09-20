<div class="panel panel-danger">
    <div class="panel-heading">
        <?php echo $video['title']; ?>
    </div>
    <div class="panel-body">
        <i class="fas fa-sync fa-spin"></i>
        <?php
        echo humanTimingAgo($isMoving['modified']);
        ?>
    </div>
</div>