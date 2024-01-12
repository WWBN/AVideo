<style>
    .row-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .colorful-text {
        margin-right: 10px;
        cursor: pointer;
    }

    .colorful-text span {
        font-size: 1.5em;
        font-weight: bold;
    }

    /* Assigning different colors and shadows to spans */
    .colorful-text span:nth-child(1) {
        color: #e6194b;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Red with white shadow */
    .colorful-text span:nth-child(2) {
        color: #3cb44b;
        text-shadow: 1px 1px 0 #000;
    }

    /* Green with black shadow */
    .colorful-text span:nth-child(3) {
        color: #ffe119;
        text-shadow: 1px 1px 0 #000;
    }

    /* Yellow with black shadow */
    .colorful-text span:nth-child(4) {
        color: #4363d8;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Blue with white shadow */
    .colorful-text span:nth-child(5) {
        color: #f58231;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Orange with white shadow */
    .colorful-text span:nth-child(6) {
        color: #911eb4;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Purple with white shadow */
    .colorful-text span:nth-child(7) {
        color: #42d4f4;
        text-shadow: 1px 1px 0 #000;
    }

    /* Light blue with black shadow */
    .colorful-text span:nth-child(8) {
        color: #f032e6;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Pink with white shadow */
    .colorful-text span:nth-child(9) {
        color: #a9a9a9;
        text-shadow: 1px 1px 0 #000;
    }

    /* Gray with black shadow */
    .colorful-text span:nth-child(10) {
        color: #fabed4;
        text-shadow: 1px 1px 0 #000;
    }

    /* Light pink with black shadow */
    .colorful-text span:nth-child(11) {
        color: #469990;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Teal with white shadow */
    .colorful-text span:nth-child(12) {
        color: #dcbeff;
        text-shadow: 1px 1px 0 #000;
    }

    /* Lilac with black shadow */
    .colorful-text span:nth-child(13) {
        color: #9A6324;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Brown with white shadow */
    .colorful-text span:nth-child(14) {
        color: #fffac8;
        text-shadow: 1px 1px 0 #000;
    }

    /* Cream with black shadow */
    .colorful-text span:nth-child(15) {
        color: #800000;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Maroon with white shadow */
    .colorful-text span:nth-child(16) {
        color: #aaffc3;
        text-shadow: 1px 1px 0 #000;
    }

    /* Mint green with black shadow */
    .colorful-text span:nth-child(17) {
        color: #808000;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Olive with white shadow */
    .colorful-text span:nth-child(18) {
        color: #ffd8b1;
        text-shadow: 1px 1px 0 #000;
    }

    /* Peach with black shadow */
    .colorful-text span:nth-child(19) {
        color: #000075;
        text-shadow: 1px 1px 0 #fff;
    }

    /* Navy with white shadow */
    .colorful-text span:nth-child(20) {
        color: #a9ffac;
        text-shadow: 1px 1px 0 #000;
    }

    /* Light green with black shadow */
</style>
<?php
function createColorfulTextSpans($string)
{
    $output = '<span class="colorful-text">';
    for ($i = 0; $i < strlen($string); $i++) {
        $char = htmlspecialchars($string[$i]);
        $output .= "<span>$char</span>";
    }
    $output .= '</span>';
    return $output;
}
$checked = '';
if (!empty($_COOKIE['forKids'])) {
    $checked = 'checked';
}
?>
<label for="forKids" class="row-label">
    <?php
    echo createColorfulTextSpans(__('For Kids'));
    ?>
    <div class="material-switch">
        <input type="checkbox" value="1" id="forKids" <?php echo $checked; ?> onchange="toogleForKids();">
        <label for="forKids" class="label-success"></label>
    </div>
</label>
<script>
    function toogleForKids() {
        modal.showPleaseWait();
        var forKids = Cookies.get('forKids');
        Cookies.set('forKids', empty(forKids) ? 1 : 0, {
            path: '/',
            expires: 365
        });
        location.reload();
    }
</script>