<?php
if (!User::isAdmin()) { return; }
foreach ([
    ['userRegistrationsChart', 'New users', 'Existing accounts grouped by registration date. Deleted accounts are not included.', 'view/report4.json.php', 'bar'],
    ['userCumulativeChart', 'User growth', 'Cumulative count of existing accounts by registration date, including active and inactive accounts.', 'view/report4.1.json.php', 'line']
] as $registrationReport) {
?>
<section class="col-md-6 col-xs-12">
    <div class="panel panel-default report-registration" data-endpoint="<?php echo $registrationReport[3]; ?>" data-chart-type="<?php echo $registrationReport[4]; ?>">
        <div class="panel-heading"><h2><?php echo __($registrationReport[1]); ?></h2><p><?php echo __($registrationReport[2]); ?></p></div>
        <div class="panel-body">
            <p class="report-feedback" role="status"></p>
            <div class="report-chart-body"><canvas id="<?php echo $registrationReport[0]; ?>" role="img" aria-label="<?php echo __($registrationReport[1]); ?>"></canvas></div>
            <details><summary><?php echo __('View data'); ?></summary><div class="report-data-scroll"><table class="table table-striped"><thead><tr><th><?php echo __('Date'); ?></th><th><?php echo __('Users'); ?></th></tr></thead><tbody></tbody></table></div></details>
            <button type="button" class="btn btn-default report-retry hidden"><?php echo __('Retry'); ?></button>
        </div>
    </div>
</section>
<?php } ?>
<script>
$(function () {
    $('.report-registration').each(function () {
        var root = $(this), canvas = root.find('canvas')[0], feedback = root.find('.report-feedback');
        function load() {
            root.find('.report-retry').addClass('hidden'); feedback.text(<?php echo json_encode(__('Loading...')); ?>);
            root.attr('aria-busy', 'true'); AVideoReports.busy(true);
            function failed() {
                root.find('.report-chart-body').hide();
                root.find('details').hide();
                feedback.text(<?php echo json_encode(__('Unable to load this report. Check your connection and try again.')); ?>).addClass('text-danger');
                root.find('.report-retry').removeClass('hidden');
            }
            $.ajax({url: webSiteRootURL + root.data('endpoint'), dataType: 'json', timeout: 60000,
                success: function (data) {
                    if (!data || typeof data !== 'object' || data.error) { failed(); return; }
                    var labels = Object.keys(data).sort();
                    if (labels.some(function (key) { return !/^\d{4}-\d{2}-\d{2}$/.test(key) || !Number.isFinite(Number(data[key])); })) { failed(); return; }
                    var values = labels.map(function (key) { return Number(data[key]); });
                    root.find('tbody').empty();
                    labels.forEach(function (label, i) { $('<tr>').append($('<td>').text(label)).append($('<td>').text(values[i].toLocaleString())).appendTo(root.find('tbody')); });
                    feedback.removeClass('text-danger').text(labels.length ? '' : <?php echo json_encode(__('No registrations recorded yet.')); ?>);
                    root.find('.report-chart-body').toggle(labels.length > 0);
                    root.find('details').toggle(labels.length > 0);
                    AVideoReports.chart(canvas, labels, [{label: <?php echo json_encode(__('Users')); ?>, data: values}], {type: root.data('chart-type'), time: true});
                }, error: failed,
                complete: function () { root.attr('aria-busy', 'false'); AVideoReports.busy(false); }
            });
        }
        root.find('.report-retry').on('click', load); load();
    });
});
</script>
