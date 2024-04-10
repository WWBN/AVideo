<?php
global $global, $config;

require_once '../videos/configuration.php';

$_page = new Page(array('Cookies'));
?>
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">Top 10 Largest Cookies</div>
        <ul class="list-group" id="cookieList"></ul>
    </div>
</div>
<script>
    function updateCookieDisplay(cookies) {
        const $list = $('#cookieList');
        $list.empty(); // Clear existing list items
        cookies.forEach(cookie => {
            var size = humanFileSize(cookie.size);
            const listItemHtml = `<li class="list-group-item">
            Cookie Name: <strong>${cookie.name}</strong>
            <span class="badge">${size}</span>
        </li>`;
            $list.append(listItemHtml);
        });
    }

    $(function() {
        updateCookieDisplay(findLargestCookies());
    });
</script>
<?php
$_page->print();
?>