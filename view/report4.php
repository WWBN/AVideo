<?php
if (!User::isAdmin()) { return; }
foreach ([
    ['userRegistrationsChart', 'New users', 'Existing accounts grouped by registration date. Deleted accounts are not included.', 'view/report4.json.php', 'bar'],
    ['userCumulativeChart', 'User growth', 'Cumulative count of existing accounts by registration date, including active and inactive accounts.', 'view/report4.1.json.php', 'line']
] as $registrationReport) {
?>
<section class="col-xs-12">
    <div class="panel panel-default report-registration" data-endpoint="<?php echo $registrationReport[3]; ?>" data-chart-type="<?php echo $registrationReport[4]; ?>">
        <div class="panel-heading report-page-heading">
            <div><h2><?php echo __($registrationReport[1]); ?></h2><p><?php echo __($registrationReport[2]); ?></p></div>
            <div class="report-actions">
                <label for="<?php echo $registrationReport[0]; ?>Period"><?php echo __('Period'); ?></label>
                <select class="form-control report-registration-period" id="<?php echo $registrationReport[0]; ?>Period">
                    <?php foreach ([30 => 'Last 30 Days', 90 => 'Last 90 Days', 365 => 'Last 365 days', 0 => 'All time'] as $days => $label) { ?>
                        <option value="<?php echo $days; ?>" <?php echo $days === 365 ? 'selected' : ''; ?>><?php echo __($label); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="panel-body">
            <p class="report-feedback" role="status" aria-live="polite"></p>
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
        var root = $(this), canvas = root.find('canvas')[0], feedback = root.find('.report-feedback'), response;
        var cumulative = root.data('chart-type') === 'line';
        var grouping = <?php echo json_encode(['day' => __('Grouped by day'), 'week' => __('Grouped by week'), 'month' => __('Grouped by month'), 'year' => __('Grouped by year')]); ?>;
        function draw() {
            if (!response) { return; }
            var series = AVideoReports.registrationSeries(response, Number(root.find('.report-registration-period').val()), cumulative, <?php echo json_encode(date('Y-m-d')); ?>);
            function formatDate(value, options) {
                return new Date(value + 'T00:00:00Z').toLocaleDateString(document.documentElement.lang || undefined, Object.assign({timeZone: 'UTC'}, options));
            }
            var ranges = series.ranges.map(function (range) {
                return range.map(function (value) { return formatDate(value, {year: 'numeric', month: 'short', day: 'numeric'}); }).join(' – ');
            });
            var labels = series.ranges.map(function (range, i) {
                var label = series.unit === 'year' && series.yearStep > 1 ? range[0].slice(0, 4) + '–' + range[1].slice(0, 4) : formatDate(range[0], series.unit === 'year' ? {year: 'numeric'} : series.unit === 'month' ? {year: 'numeric', month: 'short'} : {month: 'short', day: 'numeric'});
                return label + (series.partial[i] ? '*' : '');
            });
            root.find('tbody').empty();
            ranges.forEach(function (label, i) { $('<tr>').append($('<td>').text(label)).append($('<td>').text(series.values[i].toLocaleString())).appendTo(root.find('tbody')); });
            var description = cumulative ? <?php echo json_encode(__('Total accounts at the end of each interval. The vertical scale adjusts to show changes.')); ?> : <?php echo json_encode(__('New accounts in each interval.')); ?>;
            var groupLabel = series.unit === 'year' && series.yearStep > 1 ? <?php echo json_encode(__('Grouped in multi-year intervals')); ?> : grouping[series.unit];
            if (series.partial.some(Boolean)) { description += ' ' + <?php echo json_encode(__('* Partial interval. See View data for exact dates.')); ?>; }
            feedback.removeClass('text-danger').text(series.values.length ? groupLabel + '. ' + description : <?php echo json_encode(__('No registrations recorded yet.')); ?>);
            root.find('.report-chart-body, details').toggle(series.values.length > 0);
            AVideoReports.chart(canvas, labels, [{label: cumulative ? <?php echo json_encode(__('Total accounts')); ?> : <?php echo json_encode(__('New users')); ?>, data: series.values, maxBarThickness: 48, pointHitRadius: 12}], {type: root.data('chart-type'), fullLabels: ranges, beginAtZero: !cumulative, interaction: {mode: 'index', intersect: false}});
        }
        function load() {
            root.find('.report-retry').addClass('hidden'); feedback.text(<?php echo json_encode(__('Loading...')); ?>);
            root.attr('aria-busy', 'true'); AVideoReports.busy(true);
            function failed() {
                response = null;
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
                    response = data;
                    draw();
                }, error: failed,
                complete: function () { root.attr('aria-busy', 'false'); AVideoReports.busy(false); }
            });
        }
        root.find('.report-registration-period').on('change', draw);
        root.find('.report-retry').on('click', load); load();
    });
});
</script>
