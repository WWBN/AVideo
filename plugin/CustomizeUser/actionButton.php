<?php
if($obj->allowDonationLink){
    $u = new User($video['users_id']);
    ?>
<a class="btn btn-success no-outline" href="<?php echo $u->getDonationLink() ?>" target="_blank">
        <i class="fas fa-donate"></i> <small><?php echo __('Donation'); ?></small>
    </a>    
    <?php
}
?>