<?php
if(!AVideoPlugin::isEnabledByName('Scheduler')){
    return '';
}
if(Scheduler::isActive()){
    ?>
    <span class="glowText">
        Scheduler plugin cront tab found
    </span>
    <?php 
}else{
    ?>
    <span class="glowTextBlue">
        Scheduler plugin cront tab NOT found
    <?php 
}
?>