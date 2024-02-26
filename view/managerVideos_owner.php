<?php
$videos_id = getVideos_id();
$owner_users_id = 0;
if(!empty($videos_id)){
    $video = new Video('', '', $videos_id);
    $owner_users_id = $video->getUsers_id();
}
$myAffiliates = CustomizeUser::getCompanyAffiliates(User::getId());
if (!empty($myAffiliates)) {
    $users_id_list = [];
    $users_id_list[] = User::getId();
    foreach ($myAffiliates as $value) {
        $users_id_list[] = $value['users_id_affiliate'];
    }

    echo '<label class="control-label" for="users_id_company" >' . __("Media Owner") . '</label>';
    echo Layout::getUserSelect('inputUserOwner', $users_id_list, "", 'inputUserOwner_id', '');
} else {
?>
    <div class="row" <?php if (empty($advancedCustomUser->userCanChangeVideoOwner) && !Permissions::canAdminVideos()) { ?> style="display: none;" <?php } ?>>
        <label class="control-label" for="inputUserOwner_id"><?php echo __("Media Owner"); ?></label>
        <?php
        $updateUserAutocomplete = Layout::getUserAutocomplete($owner_users_id, 'inputUserOwner_id', []);
        ?>
    </div>
<?php
}
?>
<?php
$myAffiliation = CustomizeUser::getAffiliateCompanies(User::getId());
if (!empty($myAffiliation)) {
    $users_id_list = [];
    foreach ($myAffiliation as $value) {
        $users_id_list[] = $value['users_id_company'];
    }
    echo '<label class="control-label" for="users_id_company" >' . __("Company") . '</label>';
    echo Layout::getUserSelect('users_id_company', $users_id_list, $owner_users_id, 'users_id_company', '');
}
?>