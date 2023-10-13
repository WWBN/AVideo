<div>
    <button data-toggle="tooltip" title="<?php echo Video::$rratingOptionsText['nc-17']; ?>" class="btn btn-danger btn-outline btn-xs btn-sm" onclick="$(this).find('table').slideToggle();" style="position: relative;">
        NC-17
    </button>
    <table class="table-bordered bg-warning" style="z-index: 1;border: 2px solid #555; border-radius: 4px; margin: 2px;border-collapse: separate !important; display: none; position: absolute; left: 0;">
        <tr>
            <td style="font-size: 1.5em; text-align: center; font-weight: bold; padding: 1px 2px;">NC-17</td>
            <td style="font-size: 0.8em; text-align: center; font-weight: bold; padding: 1px 2px;"><?php echo strtoupper(__("No one 17 and under admitted")); ?></td>
        </tr>
    </table>
</div>