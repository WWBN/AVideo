<?php
// Included by the four report views; all identifiers and endpoints are internal constants.
$reportId = $report['id'];
$reportMessages = [];
foreach (['Loading...', 'Unable to load this report. Check your connection and try again.', 'No activity in this period. Try a longer date range.', 'No activity recorded yet.', 'No matching results', 'Search', 'Show _MENU_ entries', 'Showing _START_ to _END_ of _TOTAL_ entries', 'Showing 0 entries', '(filtered from _MAX_ total entries)', 'Next', 'Previous', 'First', 'Last', 'Choose a valid start and end date.', 'The start date must be on or before the end date.', 'Select a user from the suggestions or clear the field.', 'All time', 'Results', 'Views', 'Watch time', 'Likes', 'Dislikes', 'Total', 'Open video details'] as $message) {
    $reportMessages[$message] = __($message);
}
$report['messages'] = $reportMessages;
?>
<section class="report-section" id="<?php echo $reportId; ?>Report">
    <header class="report-section-heading">
        <h2><?php echo __($report['title']); ?></h2>
        <p class="text-muted"><?php echo __($report['description']); ?></p>
    </header>
    <form class="report-filters panel panel-default">
        <div class="panel-body report-filter-grid">
            <?php if (!empty($report['dated'])) { ?>
                <div class="form-group">
                    <label for="<?php echo $reportId; ?>Period"><?php echo __('Period'); ?></label>
                    <select id="<?php echo $reportId; ?>Period" class="form-control report-period">
                        <option value="7"><?php echo __('Last 7 Days'); ?></option>
                        <option value="30" selected><?php echo __('Last 30 Days'); ?></option>
                        <option value="90"><?php echo __('Last 90 Days'); ?></option>
                        <option value="custom"><?php echo __('Custom dates'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="<?php echo $reportId; ?>From"><?php echo __('From'); ?></label>
                    <input id="<?php echo $reportId; ?>From" name="dateFrom" type="date" required class="form-control" value="<?php echo date('Y-m-d', strtotime('-29 days')); ?>">
                </div>
                <div class="form-group">
                    <label for="<?php echo $reportId; ?>To"><?php echo __('To'); ?></label>
                    <input id="<?php echo $reportId; ?>To" name="dateTo" type="date" required class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            <?php } else { ?>
                <p class="help-block"><?php echo __('All time'); ?></p>
            <?php } ?>
            <?php if (!empty($report['owner']) && Permissions::canAdminUsers()) { ?>
                <div class="form-group">
                    <label for="inputUserOwner"><?php echo __('Video owner'); ?></label>
                    <input id="inputUserOwner" class="form-control" placeholder="<?php echo __('All users'); ?>" autocomplete="off">
                    <input id="inputUserOwner_id" name="users_id" type="hidden" value="0">
                </div>
            <?php } ?>
            <div class="report-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh" aria-hidden="true"></i> <?php echo __('Update report'); ?></button>
                <?php if (!empty($report['owner'])) { ?>
                    <button type="button" class="btn btn-default report-export" title="<?php echo __('Exports the selected period and owner, including rows hidden by the table search.'); ?>"><i class="fas fa-file-csv" aria-hidden="true"></i> <?php echo __('Download CSV (all results)'); ?></button>
                <?php } ?>
            </div>
        </div>
    </form>
    <p class="report-feedback text-muted" role="status" aria-live="polite"></p>
    <div class="report-summary" aria-live="polite"></div>
    <div class="table-responsive">
        <table id="<?php echo $reportId; ?>" class="table table-striped table-hover report-table">
            <caption class="sr-only"><?php echo __($report['title']); ?></caption>
            <thead><tr><?php foreach ($report['columns'] as $column) { ?><th scope="col"><?php echo __($column['title']); ?></th><?php } ?></tr></thead>
        </table>
    </div>
</section>
<script>$(function () { AVideoReports.table(<?php echo json_encode($report, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>); });</script>
