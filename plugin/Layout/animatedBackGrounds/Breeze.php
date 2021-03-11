<?php
$total = 20;
$minSize = 20;
$maxSize = 160;
?>
<style>
    body{
        background: radial-gradient(#AAA, #333);
    }
    .bg-bubbles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .bg-bubbles li {
        position: absolute;
        list-style: none;
        display: block;
        width: 40px;
        height: 40px;
        background-color: #777777AA;
        bottom: -160px;
        -webkit-animation: square 35s infinite;
        animation: square 35s infinite;
        transition-timing-function: linear;
    }
    <?php
    for ($i = 1; $i <= $total; $i++) {
        $size = rand($minSize, $maxSize);
        
        $minDuration = 10;
        $maxDuration = 30;
        
        if($size>100){
            $minDuration = 20;
            $maxDuration = 40;
        }else if($size>60){
            $minDuration = 15;
            $maxDuration = 35;
        }
        
        ?>
        .bg-bubbles li:nth-child(<?php echo $i; ?>) {
            left: <?php echo mt_rand(0, 1) ? mt_rand(10, 30) : mt_rand(70, 90); ?>%;
            width: <?php echo $size; ?>px;
            height: <?php echo $size; ?>px;
            -webkit-animation-delay: <?php echo rand(0, 5); ?>s;
            animation-delay: <?php echo rand(0, 5); ?>s;
            -webkit-animation-duration: <?php echo rand($minDuration, $maxDuration); ?>s;
            animation-duration: <?php echo rand($minDuration, $maxDuration); ?>s;
            background-color: rgba(127,127,127,0.<?php echo rand(2, 8); ?>);
            border-radius: <?php echo rand(0, $size/2); ?>px;
        }    
        <?php
    }
    ?>

    @-webkit-keyframes square {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-1200px) rotate(600deg);
        }
    }
    @keyframes square {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-1200px) rotate(600deg);
        }
    }
</style>
<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>