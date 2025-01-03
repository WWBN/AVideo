<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once __DIR__ . '/../../videos/configuration.php';
}

//VideoPlaylistScheduler::executeOnHalfHourSchedule(true);exit;
$_page = new Page(array('Events Calendar'));
$_page->setExtraScripts(
    array(
        'node_modules/@fullcalendar/core/index.global.min.js',
        'node_modules/@fullcalendar/interaction/index.global.min.js',
        'node_modules/@fullcalendar/daygrid/index.global.min.js',
        'node_modules/@fullcalendar/list/index.global.min.js',
        'node_modules/@fullcalendar/timegrid/index.global.min.js',
    )
);

?>
<style>
    #calendar table {
        background-color: transparent !important;
    }
</style>
<div class="container-fluid">
    <div id='calendar'></div>
</div>
<script>
    $(document).ready(function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today', // Navigation buttons
                center: 'title', // Calendar title
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' // Added `timelineYear`
            },
            views: {
                timelineYear: {
                    type: 'timeline',
                    duration: {
                        years: 1
                    },
                    buttonText: 'Year',
                }
            },
            selectable: false,
            editable: false,
            nowIndicator: true,
            allDaySlot: false,
            events: webSiteRootURL + 'plugin/Live/calendar.json.php'
        });

        calendar.render();
    });
</script>
<?php
$_page->print();
?>