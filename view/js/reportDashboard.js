/* Shared interactions for the analytics tabs. Uses the bundled DataTables and Chart.js. */
window.AVideoReports = (function ($) {
    'use strict';
    var pending = 0;
    function busy(start) {
        pending = Math.max(0, pending + (start ? 1 : -1));
        if (start && pending === 1) { modal.showPleaseWait(); }
        if (!pending) { modal.hidePleaseWait(); }
    }
    function escape(value) { return $('<span>').text(value == null ? '' : value).html(); }
    function number(value) { return (Number(value) || 0).toLocaleString(); }
    function table(config) {
        var root = $('#' + config.id + 'Report'), form = root.find('form'), grid;
        var feedback = root.find('.report-feedback'), summary = root.find('.report-summary');
        var messages = config.messages;
        function t(key) { return messages[key] || key; }
        function parameters() {
            var data = {};
            form.serializeArray().forEach(function (field) { data[field.name] = field.value; });
            return data;
        }
        function validate() {
            var data = parameters(), message = '';
            if (config.dated && (!data.dateFrom || !data.dateTo || !form[0].checkValidity())) {
                message = t('Choose a valid start and end date.');
            } else if (config.dated && data.dateFrom > data.dateTo) {
                message = t('The start date must be on or before the end date.');
            } else if (root.find('#inputUserOwner').val() && !Number(data.users_id)) {
                message = t('Select a user from the suggestions or clear the field.');
            }
            feedback.toggleClass('text-danger', !!message).text(message);
            if (message) { avideoToastError(message); return false; }
            return true;
        }
        form.find('.report-period').on('change', function () {
            if (this.value === 'custom') { return; }
            var end = new Date(), start = new Date();
            start.setDate(end.getDate() - Number(this.value) + 1);
            function iso(date) { return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0'); }
            form.find('[name=dateFrom]').val(iso(start));
            form.find('[name=dateTo]').val(iso(end));
        });
        form.find('input[type=date]').on('change', function () { form.find('.report-period').val('custom'); });
        var owner = root.find('#inputUserOwner');
        if (owner.length) {
            owner.on('input', function () { root.find('#inputUserOwner_id').val(0); });
            owner.autocomplete({
                minLength: 1,
                source: function (request, respond) {
                    $.ajax({url: webSiteRootURL + 'objects/users.json.php', type: 'POST', dataType: 'json', timeout: 30000,
                        data: {searchPhrase: request.term}, success: function (data) { respond(data.rows || []); },
                        error: function () { respond([]); avideoToastError(t('Unable to load this report. Check your connection and try again.')); }});
                },
                focus: function () { return false; },
                select: function (event, ui) { owner.val(ui.item.user); root.find('#inputUserOwner_id').val(ui.item.id); return false; }
            }).autocomplete('instance')._renderItem = function (ul, item) {
                return $('<li>').append($('<div>').text(item.user || item.name)).appendTo(ul);
            };
        }
        function updateSummary(rows) {
            summary.empty();
            config.columns.slice(1).forEach(function (column) {
                var total = rows.reduce(function (sum, row) { return sum + (Number(row[column.data]) || 0); }, 0);
                var value = column.duration ? Math.floor(total / 3600) + ':' + String(Math.floor(total % 3600 / 60)).padStart(2, '0') + ':' + String(Math.floor(total % 60)).padStart(2, '0') : number(total);
                summary.append($('<div class="panel panel-default report-summary-item">').append($('<span>').text(t(column.title))).append($('<strong>').text(value)));
            });
        }
        function load() {
            if (!root.is(':visible') || root.attr('aria-busy') === 'true' || !validate()) { return; }
            if (grid) { grid.ajax.reload(); return; }
            grid = $('#' + config.id).DataTable({
                order: [[1, 'desc']], pageLength: 10, autoWidth: false,
                drawCallback: function () { updateSummary(this.api().rows({search: 'applied'}).data().toArray()); },
                language: {emptyTable: t(config.dated ? 'No activity in this period. Try a longer date range.' : 'No activity recorded yet.'), zeroRecords: t('No matching results'),
                    search: t('Search') + ':', lengthMenu: t('Show _MENU_ entries'), info: t('Showing _START_ to _END_ of _TOTAL_ entries'),
                    infoEmpty: t('Showing 0 entries'), infoFiltered: t('(filtered from _MAX_ total entries)'),
                    paginate: {next: t('Next'), previous: t('Previous'), first: t('First'), last: t('Last')}},
                ajax: function (request, callback) {
                    var params = parameters();
                    root.attr('aria-busy', 'true'); form.find('button').prop('disabled', true); busy(true);
                    feedback.removeClass('text-danger').text(t('Loading...')); summary.empty();
                    function failed() {
                        callback({data: []});
                        summary.empty();
                        root.find('td.dataTables_empty').text(t('Unable to load this report. Check your connection and try again.'));
                        feedback.addClass('text-danger').text(t('Unable to load this report. Check your connection and try again.'));
                    }
                    $.ajax({url: webSiteRootURL + config.endpoint, type: 'POST', dataType: 'json', timeout: 60000, data: params,
                        success: function (response) {
                            if (!response || response.error || !Array.isArray(response.data)) { failed(); return; }
                            callback(response);
                            feedback.text((config.dated ? params.dateFrom + ' — ' + params.dateTo : t('All time')) + ' · ' + number(response.data.length) + ' ' + t('Results'));

                        }, error: failed,
                        complete: function () { root.attr('aria-busy', 'false'); form.find('button').prop('disabled', false); busy(false); }
                    });
                },
                columns: config.columns.map(function (column, index) {
                    return {data: column.data, defaultContent: '', className: index ? 'text-right' : '',
                        render: function (data, type, row) {
                            if (column.duration) { return type === 'display' ? escape(row.seconds_watching_video_human || '00:00:00') : Number(data) || 0; }
                            if (index) { return type === 'display' ? number(data) : Number(data) || 0; }
                            if (config.owner && type === 'display') {
                                return '<button type="button" class="btn btn-link report-video-details" data-video-id="' + (parseInt(row.videos_id, 10) || 0) + '" title="' + escape(t('Open video details')) + '">' + escape(data) + '</button>';
                            }
                            return data;
                        }};
                })
            });
        }
        form.on('submit', function (event) { event.preventDefault(); load(); });
        root.find('.report-export').on('click', function () {
            if (validate()) { window.location.href = webSiteRootURL + 'view/videoViewsAnWatchingTime.csv.php?' + $.param(parameters()); }
        });
        root.on('click', '.report-video-details', function () { avideoModalIframe(webSiteRootURL + 'view/videoViewsInfo.php?videos_id=' + $(this).data('video-id')); });
        $('a[data-toggle="tab"]').on('shown.bs.tab', function () { if (root.is(':visible')) { if (grid) { grid.columns.adjust(); } else { load(); } } });
        load();
    }
    function chart(canvas, labels, datasets, options) {
        var previous = Chart.getChart(canvas);
        if (previous) { previous.destroy(); }
        var styles = getComputedStyle(canvas.parentElement);
        var colors = [styles.getPropertyValue('--report-accent').trim() || '#009fe2', styles.getPropertyValue('--report-highlight').trim() || '#fcc70d'];
        datasets.forEach(function (dataset, index) {
            dataset.backgroundColor = colors[index % colors.length];
            dataset.borderColor = colors[index % colors.length];
            dataset.borderWidth = 1;
            dataset.pointRadius = 2;
        });
        options = options || {};
        return new Chart(canvas, {
            type: options.type || 'bar', data: {labels: labels, datasets: datasets},
            options: {
                responsive: true, maintainAspectRatio: false,
                indexAxis: options.horizontal ? 'y' : 'x',
                animation: {duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 250},
                scales: {
                    x: {type: options.time ? 'time' : (options.horizontal ? 'linear' : 'category'), beginAtZero: true, ticks: {color: styles.color, maxRotation: 0, precision: 0}},
                    y: {beginAtZero: true, ticks: {color: styles.color, precision: 0}}
                },
                plugins: {legend: {display: datasets.length > 1, labels: {color: styles.color}}, tooltip: {callbacks: {
                    title: function (items) { return options.fullLabels ? options.fullLabels[items[0].dataIndex] : items[0].label; }
                }}}
            }
        });
    }
    return {table: table, busy: busy, chart: chart};
})(jQuery);
