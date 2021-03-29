<?php
$channels = Subscribe::getSubscribedChannels(User::getId());
foreach ($channels as $value) {
    $_POST['disableAddTo'] = 0;
    createChannelItem($value['users_id'], $value['photoURL'], $value['identification'], $obj->SubscribedChannelsRowCount);
}