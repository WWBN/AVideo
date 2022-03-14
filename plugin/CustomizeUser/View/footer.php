<?php
$notifications = CustomizeUser::getNotifications();
?>
<!-- CustomizeUser Footer -->
<script>
    $(document).ready(function () {
<?php
foreach ($notifications as $value) {
    if (!empty($value['js'])) {
        echo $value['js'];
    }
}
?>
    });
</script>