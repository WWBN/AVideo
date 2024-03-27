<?php

require_once '../../videos/configuration.php';

$obj = AVideoPlugin::getObjectData('MonetizeUsers');
$obj2 = AVideoPlugin::getObjectData('YPTWallet');
$rewardPerView = $obj->rewardPerView;
$_page = new Page(array('Revenue Calculator'));

?>
<style>
    #custom-handle {
        width: 4em;
        height: 1.6em;
        top: 50%;
        margin-top: -.8em;
        text-align: center;
        line-height: 1.6em;
    }

    #views-slider {
        height: 15px;
    }

    #rewardDetails .panel-body {
        font-size: 1.5em;
        font-weight: bold;
    }
</style>
<div class="container">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h1><?php echo __('Daily views'); ?></h1>
        </div>
        <div class="panel-body">
            <div id="views-slider">
                <div id="custom-handle" class="ui-slider-handle"></div>
            </div>
            <div class="row" id="rewardDetails">
                <div class="col-sm-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            echo __('Views');
                            ?>
                        </div>
                        <div class="panel-body">
                            <span id="views-count">0 views</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            echo __('Estimated daily earnings');
                            ?>
                        </div>
                        <div class="panel-body">
                            <span id="daily-earnings">$0</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            echo __('Estimated monthly earnings');
                            ?>
                        </div>
                        <div class="panel-body">
                            <span id="monthly-earnings">$0</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            echo __('Estimated yearly projection');
                            ?>
                        </div>
                        <div class="panel-body">
                            <span id="yearly-projection">$0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <?php
            if(empty($obj->revenueCalculatorFooterText->value)){
                ?>
                <h3>Understanding Your Revenue</h3>
                <p>The revenue you earn from your videos is directly tied to the views they accumulate. Simply put, more views translate to more earnings. Here's how it works:</p>
                <p>Each view on your videos contributes to your overall revenue. Currently, the value per view is set at <strong><?php echo YPTWallet::formatCurrency($rewardPerView); ?></strong>.</p>
                <p>This means, as your videos are viewed across our platform, each view adds to your earnings based on the above rate. This system ensures that your content creation efforts are rewarded directly in relation to your video's popularity and viewership.</p>
                <h4>How to Maximize Your Earnings</h4>
                <ul>
                    <li><strong>Quality Content:</strong> High-quality, engaging content is more likely to attract views.</li>
                    <li><strong>Promotion:</strong> Share your videos on social media and other platforms to increase visibility.</li>
                    <li><strong>Engagement:</strong> Interact with your audience through comments to build a community around your channel.</li>
                </ul>
                <p>By understanding this revenue model, you can better strategize how to produce and promote your videos to maximize your earnings. Keep creating, keep engaging, and watch your revenue grow!</p>
                <?php
            }else{
               echo $obj->revenueCalculatorFooterText->value;
            }
            ?>
        </div>
    </div>
</div>
<script>
    $(function() {
        var handle = $("#custom-handle");
        var slider = $("#views-slider").slider({
            range: "min",
            value: 0,
            min: 0,
            max: 1000, // Adjust the max value to 1000 steps
            create: function() {
                handle.text($(this).slider("value"));
            },
            slide: function(event, ui) {
                var actualValue = calculateActualValue(ui.value);
                updateDisplay(actualValue);
                updateEarnings(actualValue);
            }
        });

        function calculateActualValue(sliderValue) {
            var maxSliderValue = slider.slider("option", "max");
            var firstThreshold = maxSliderValue * 0.5; // Adjusting for 50% of the new max slider value
            var secondThreshold = maxSliderValue * 0.75; // Adjusting for 75% of the new max slider value
            var actualValue;

            if (sliderValue <= firstThreshold) {
                // Map the first 50% of slider range to 0 - 1,000 views
                actualValue = (sliderValue / firstThreshold) * 1000;
            } else if (sliderValue <= secondThreshold) {
                // Map 50% to 75% of slider range to 1,000 - 100,000 views
                var percentageOfRange = (sliderValue - firstThreshold) / (secondThreshold - firstThreshold);
                actualValue = 1000 + (percentageOfRange * (100000 - 1000));
            } else {
                // Map the last 25% of slider range to 100,000 - 10,000,000 views
                var percentageOfRange = (sliderValue - secondThreshold) / (maxSliderValue - secondThreshold);
                actualValue = 100000 + (percentageOfRange * (10000000 - 100000));
            }

            return actualValue;
        }


        function updateDisplay(views) {
            var number = formatNumber(views);
            $("#views-count").text(number + ' views');
            handle.text(number);
        }

        function updateEarnings(views) {
            var rewardPerView = <?php echo json_encode($rewardPerView); ?>;
            var dailyEarnings = views * rewardPerView;
            var monthlyEarnings = dailyEarnings * 30;
            var yearlyEarnings = dailyEarnings * 365;

            $("#daily-earnings").text('$' + formatNumber(dailyEarnings));
            $("#monthly-earnings").text('$' + formatNumber(monthlyEarnings));
            $("#yearly-projection").text('$' + formatNumber(yearlyEarnings));
        }
    });
</script>
<?php
$_page->print();
?>