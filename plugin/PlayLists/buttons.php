<?php
// disabled for now
if(!User::isLogged() || true){
    return "";
}
?>
<span style="position: absolute; top: 5px; right: 5px;">
    <?php if(PlayLists::isVideoOnWatchLater($videos_id, User::getId())){ ?>
    <button class="btn btn-sm btn-xs" style="background-color: #FFFFFF00"><i class="fas fa-clock"></i></button>
    <?php }  else {?>
    <button class="btn btn-sm btn-xs" style="background-color: #FFFFFF00"><i class="far fa-clock"></i></button>
    <?php
    } ?>
    <?php if(PlayLists::isVideoOnFavorite($videos_id, User::getId())){ ?>
    <button class="btn btn-sm btn-xs" style="background-color: #FFFFFF00"><i class="fas fa-heart"></i></button>
    <?php }  else {?>
    <button class="btn btn-sm btn-xs" style="background-color: #FFFFFF00"><i class="far fa-heart"></i></button>
    <?php
    } ?>
</span>