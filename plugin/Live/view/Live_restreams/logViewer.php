<?php
/**
 * FFMPEG Log Viewer
 * Shows the restream FFMPEG log to the stream owner or admin.
 * Can auto-refresh while the stream is active.
 */
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams_logs.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';

if (!AVideoPlugin::isEnabledByName('Live')) {
    forbiddenPage('Plugin is disabled');
}

if (!Live::canRestream()) {
    forbiddenPage(__("You can not do this"));
}

$live_restreams_logs_id       = intval(@$_REQUEST['live_restreams_logs_id']);
$live_transmitions_history_id = intval(@$_REQUEST['live_transmitions_history_id']);
$live_restreams_id            = intval(@$_REQUEST['live_restreams_id']);

if (!empty($live_restreams_logs_id)) {
    $lrl = new Live_restreams_logs($live_restreams_logs_id);
    $live_transmitions_history_id = $lrl->getLive_transmitions_history_id();
    $live_restreams_id            = $lrl->getLive_restreams_id();
}

if (empty($live_transmitions_history_id) || empty($live_restreams_id)) {
    die(__('Missing required parameters'));
}

if (!User::isAdmin()) {
    $lr = new Live_restreams($live_restreams_id);
    if ($lr->getUsers_id() !== User::getId()) {
        forbiddenPage(__("You have no access to this restream"));
        exit;
    }
}

$_page = new Page(array('Restream Log Viewer'));
$_page->loadBasicCSSAndJS();
?>
<style>
    #logViewerWrap {
        background: #1e1e1e;
        color: #d4d4d4;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        padding: 14px;
        border-radius: 6px;
        overflow-y: auto;
        max-height: 500px;
        white-space: pre-wrap;
        word-break: break-all;
        line-height: 1.5;
    }
    #logViewerWrap .log-error   { color: #f44747; font-weight: bold; }
    #logViewerWrap .log-warning { color: #dcdcaa; }
    #logViewerWrap .log-info    { color: #9cdcfe; }
    #logViewerWrap .log-normal  { color: #d4d4d4; }
    #logStatus { font-size: 13px; margin-bottom: 8px; }
    #logStatus .badge-active   { background: #28a745; }
    #logStatus .badge-inactive { background: #6c757d; }
    .log-explanations .alert { margin-bottom: 6px; font-size: 13px; }
    #logViewerContainer { position: relative; }
    #autoScrollToggle { font-size: 12px; }
</style>

<div class="container-fluid" style="padding-top:16px;">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <strong><i class="fas fa-terminal"></i> <?php echo __('Restream FFMPEG Log'); ?></strong>
            <div class="pull-right btn-group btn-group-sm">
                <button class="btn btn-default" id="autoScrollToggle" onclick="toggleAutoScroll();">
                    <i class="fas fa-arrow-down"></i> <?php echo __('Auto-scroll: ON'); ?>
                </button>
                <button class="btn btn-info" onclick="loadLog();">
                    <i class="fas fa-sync"></i> <?php echo __('Refresh'); ?>
                </button>
            </div>
        </div>
        <div class="panel-body">
            <div id="logHistoryWrap" style="margin-bottom:10px;display:none;">
                <label for="logHistorySelect" class="control-label" style="font-size:12px;">
                    <i class="fas fa-history"></i> <?php echo __('Session history for this destination:'); ?>
                </label>
                <select id="logHistorySelect" class="form-control input-sm" onchange="switchLogEntry(this.value);" style="max-width:480px;">
                </select>
            </div>

            <div id="logStatus">
                <span class="badge badge-inactive" id="logStatusBadge"><?php echo __('Loading…'); ?></span>
                &nbsp;
                <small class="text-muted" id="logStatusTime"></small>
            </div>

            <div id="logViewerContainer">
                <pre id="logViewerWrap"><span class="text-muted"><?php echo __('Loading log content…'); ?></span></pre>
            </div>

            <div id="logExplanations" class="log-explanations" style="margin-top:14px;"></div>
        </div>
    </div>
</div>

<script>
var _logAutoScroll = true;
var _logRefreshInterval = null;
var _logLiveTransmitionsHistoryId = <?php echo intval($live_transmitions_history_id); ?>;
var _logLiveRestreamsId = <?php echo intval($live_restreams_id); ?>;
var _logLiveRestreamsLogsId = <?php echo intval($live_restreams_logs_id); ?>;

// Known FFMPEG error patterns with human-readable explanations
var _ffmpegErrorPatterns = [
    {
        pattern: /Connection refused/i,
        level: 'danger',
        title: '<?php echo addslashes(__('Connection Refused')); ?>',
        msg: '<?php echo addslashes(__('The destination RTMP server refused the connection. Check the stream URL and make sure the server is online.')); ?>'
    },
    {
        pattern: /Invalid data found when processing input/i,
        level: 'danger',
        title: '<?php echo addslashes(__('Invalid Stream Data')); ?>',
        msg: '<?php echo addslashes(__('The input stream contains invalid data. The source HLS may not be ready yet or the stream was interrupted.')); ?>'
    },
    {
        pattern: /No such file or directory/i,
        level: 'danger',
        title: '<?php echo addslashes(__('File Not Found')); ?>',
        msg: '<?php echo addslashes(__('A required file or path was not found on the restreamer server.')); ?>'
    },
    {
        pattern: /403 Forbidden/i,
        level: 'danger',
        title: '<?php echo addslashes(__('Access Forbidden (403)')); ?>',
        msg: '<?php echo addslashes(__('The destination server returned 403. The stream key may be invalid or expired.')); ?>'
    },
    {
        pattern: /401 Unauthorized/i,
        level: 'danger',
        title: '<?php echo addslashes(__('Unauthorized (401)')); ?>',
        msg: '<?php echo addslashes(__('The destination server requires authentication. Check the stream key.')); ?>'
    },
    {
        pattern: /rtmp.*connect.*failed/i,
        level: 'danger',
        title: '<?php echo addslashes(__('RTMP Connection Failed')); ?>',
        msg: '<?php echo addslashes(__('Could not connect to the RTMP destination. Verify the stream URL is correct and the service is active.')); ?>'
    },
    {
        pattern: /Broken pipe/i,
        level: 'warning',
        title: '<?php echo addslashes(__('Broken Pipe')); ?>',
        msg: '<?php echo addslashes(__('The connection to the destination was dropped mid-stream. This may be a temporary network issue; FFMPEG will try to reconnect.')); ?>'
    },
    {
        pattern: /Network is unreachable/i,
        level: 'danger',
        title: '<?php echo addslashes(__('Network Unreachable')); ?>',
        msg: '<?php echo addslashes(__('The restreamer server cannot reach the destination. Check network connectivity.')); ?>'
    },
    {
        pattern: /End of file/i,
        level: 'warning',
        title: '<?php echo addslashes(__('End of File / Stream Ended')); ?>',
        msg: '<?php echo addslashes(__('The source stream ended. If the live was stopped intentionally this is normal.')); ?>'
    },
    {
        pattern: /DTS .* out of order/i,
        level: 'warning',
        title: '<?php echo addslashes(__('Timestamp Out of Order')); ?>',
        msg: '<?php echo addslashes(__('The stream has timestamp discontinuities. This can cause buffering or playback issues on the destination platform.')); ?>'
    },
    {
        pattern: /too many packets buffered/i,
        level: 'warning',
        title: '<?php echo addslashes(__('Buffer Overflow')); ?>',
        msg: '<?php echo addslashes(__('FFMPEG is buffering too many packets, usually caused by an upload speed too slow for the selected bitrate.')); ?>'
    },
    {
        pattern: /SSL.*error/i,
        level: 'danger',
        title: '<?php echo addslashes(__('SSL / TLS Error')); ?>',
        msg: '<?php echo addslashes(__('An SSL/TLS error occurred. The FFMPEG on the restreamer server may not be compiled with OpenSSL support, which is required for RTMPS destinations (e.g. Facebook).')); ?>'
    }
];

function toggleAutoScroll() {
    _logAutoScroll = !_logAutoScroll;
    var btn = document.getElementById('autoScrollToggle');
    btn.innerHTML = _logAutoScroll
        ? '<i class="fas fa-arrow-down"></i> <?php echo addslashes(__('Auto-scroll: ON')); ?>'
        : '<i class="fas fa-minus"></i> <?php echo addslashes(__('Auto-scroll: OFF')); ?>';
}

function _colorizeLog(text) {
    var lines = text.split('\n');
    var out = [];
    lines.forEach(function(line) {
        var cls = 'log-normal';
        if (/error|failed|fatal/i.test(line))   cls = 'log-error';
        else if (/warning|warn/i.test(line))     cls = 'log-warning';
        else if (/info|stream|codec|video|audio/i.test(line)) cls = 'log-info';
        var safe = line.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#x27;');
        out.push('<span class="' + cls + '">' + safe + '</span>');
    });
    return out.join('\n');
}

function _escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#x27;');
}

function _buildExplanations(text) {
    var found = [];
    var seen = {};
    _ffmpegErrorPatterns.forEach(function(p) {
        if (!seen[p.title] && p.pattern.test(text)) {
            seen[p.title] = true;
            found.push(p);
        }
    });
    var html = '';
    if (found.length === 0) return '';
    html += '<h5><i class="fas fa-lightbulb"></i> <?php echo addslashes(__('Detected Issues')); ?></h5>';
    found.forEach(function(p) {
        html += '<div class="alert alert-' + p.level + '">'
              + '<strong>' + p.title + '</strong> &mdash; ' + p.msg
              + '</div>';
    });
    return html;
}

function loadLog() {
    var url = webSiteRootURL + 'plugin/Live/view/Live_restreams/getLogContent.json.php';
    if (_logLiveRestreamsLogsId) {
        // logs_id alone is enough — server resolves the rest
        url = addQueryStringParameter(url, 'live_restreams_logs_id', _logLiveRestreamsLogsId);
    } else {
        url = addQueryStringParameter(url, 'live_transmitions_history_id', _logLiveTransmitionsHistoryId);
        url = addQueryStringParameter(url, 'live_restreams_id', _logLiveRestreamsId);
    }

    $.ajax({
        url: url,
        success: function(response) {
            var wrap = document.getElementById('logViewerWrap');
            var badge = document.getElementById('logStatusBadge');
            var timeEl = document.getElementById('logStatusTime');
            var explEl = document.getElementById('logExplanations');

            if (response.error) {
                wrap.innerHTML = '<span class="log-error">' + _escapeHtml(response.msg) + '</span>';
                badge.className = 'badge badge-danger';
                badge.textContent = '<?php echo addslashes(__('Error')); ?>';
                _stopAutoRefresh();
                return;
            }

            var content = response.content || '';
            wrap.innerHTML = content ? _colorizeLog(content) : '<span class="text-muted"><?php echo addslashes(__('Log file is empty or not yet written.')); ?></span>';

            if (response.isActive) {
                badge.className = 'badge badge-active';
                badge.textContent = '<?php echo addslashes(__('Live — streaming')); ?>';
                _startAutoRefresh();
            } else {
                badge.className = 'badge badge-inactive';
                badge.textContent = '<?php echo addslashes(__('Stopped')); ?>';
                _stopAutoRefresh();
            }

            if (response.modified) {
                var d = new Date(response.modified * 1000);
                timeEl.textContent = '<?php echo addslashes(__('Last update:')); ?> ' + d.toLocaleString();
            }

            explEl.innerHTML = _buildExplanations(content);

            if (_logAutoScroll) {
                wrap.scrollTop = wrap.scrollHeight;
            }
        },
        error: function() {
            document.getElementById('logViewerWrap').innerHTML =
                '<span class="log-error"><?php echo addslashes(__('Could not reach the server.')); ?></span>';
            _stopAutoRefresh();
        }
    });
}

function _startAutoRefresh() {
    if (!_logRefreshInterval) {
        _logRefreshInterval = setInterval(loadLog, 5000);
    }
}

function _stopAutoRefresh() {
    if (_logRefreshInterval) {
        clearInterval(_logRefreshInterval);
        _logRefreshInterval = null;
    }
}

$(document).ready(function() {
    loadLog();
    loadLogHistory();
});

function loadLogHistory() {
    if (!_logLiveRestreamsId) return;
    $.getJSON(webSiteRootURL + 'plugin/Live/view/Live_restreams/logHistory.json.php', {
        live_restreams_id: _logLiveRestreamsId
    }, function(response) {
        if (response.error || !response.rows || response.rows.length < 2) return;

        var sel = document.getElementById('logHistorySelect');
        sel.innerHTML = '';
        response.rows.forEach(function(row) {
            var opt = document.createElement('option');
            opt.value = row.id;
            opt.textContent = '#' + row.id + ' — ' + row.logFile;
            // Mark current entry as selected
            if (row.id === _logLiveRestreamsLogsId ||
                row.live_transmitions_history_id === _logLiveTransmitionsHistoryId) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });

        document.getElementById('logHistoryWrap').style.display = '';
    });
}

function switchLogEntry(logs_id) {
    logs_id = parseInt(logs_id, 10);
    if (!logs_id) return;
    _stopAutoRefresh();
    _logLiveRestreamsLogsId = logs_id;
    loadLog();
}
</script>

<?php
$_page->print();
?>
