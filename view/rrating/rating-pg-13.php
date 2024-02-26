<div>
    <button data-toggle="tooltip"  title="<?php echo Video::$rratingOptionsText['pg-13']; ?>" class="btn btn-warning btn-outline btn-xs btn-sm" onclick="$(this).parent().find('table').slideToggle();" style="position: relative;">
        PG-13
    </button>
    <table class="table-bordered bg-warning" style="z-index: 1;border: 2px solid #555; border-radius: 4px; margin: 2px;border-collapse: separate !important; display: none; position: absolute; left: 0;">
        <tr>
            <td style="font-size: 1.5em; text-align: center; font-weight: bold; padding: 1px 2px;">PG-13</td>
            <td style="font-size: 0.8em; text-align: center; font-weight: bold; padding: 1px 2px;"><?php echo strtoupper(__("Parental Guidance Suggested")); ?></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 0.7em; text-align: center; padding: 1px 2px;"><?php echo strtoupper(__("Some material may not be inapropriate for children under 13")); ?></td>
        </tr>
    </table>
</div>