<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    forbiddenPage("You can not manager plugin Audit");
    exit;
}
$_page = new Page(array('Audit'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-body">
            <table id="auditTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Method</th>
                        <th>Statement</th>
                        <th>Format</th>
                        <th>Values</th>
                        <th>Created</th>
                        <th>User</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>Class</th>
                        <th>Method</th>
                        <th>Statement</th>
                        <th>Format</th>
                        <th>Values</th>
                        <th>Created</th>
                        <th>User</th>
                        <th>IP</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var auditTable = $('#auditTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo $global['webSiteRootURL']; ?>plugin/Audit/page/audits.json.php",
            },
            "columns": [{
                    "data": "class"
                },
                {
                    "data": "method"
                },
                {
                    "data": "statement"
                },
                {
                    "data": "formats"
                },
                {
                    "data": "values"
                },
                {
                    "data": "created"
                },
                {
                    "data": "user"
                },
                {
                    "data": "ip"
                },
            ],
            select: true,
            "order": [
                [5, "desc"]
            ]
        });
    });
</script>
<?php
$_page->print();
?>