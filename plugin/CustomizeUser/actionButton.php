<?php
if ($obj->allowDonationLink && !empty($video['users_id'])) {
    $u = new User($video['users_id']);
    $donationLink = $u->getDonationLink();
    if (!empty($donationLink)) {
        ?>
        <a class="btn btn-success no-outline" href="<?php echo $donationLink; ?>" target="_blank">
            <i class="fas fa-donate"></i> <small><?php echo __('Donation'); ?></small>
        </a>    
        <?php
    }
}
?>