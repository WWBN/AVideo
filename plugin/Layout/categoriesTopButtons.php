<link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/jquery-bootstrap-scrolling-tabs/jquery.scrolling-tabs.min.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/jquery-bootstrap-scrolling-tabs/jquery.scrolling-tabs.min.js" type="text/javascript"></script>
<center>
<ul class="nav nav-tabs nav-tabs-horizontal" style="display: -webkit-box; display: -webkit-inline-box;">
    <?php
    $_rowCount = getRowCount();
    $_REQUEST['rowCount'] = 1000;
    $parsed_cats = array();
    $categories = Category::getAllCategories();
    foreach ($categories as $value) {
        if ($value['parentId']) {
            continue;
        }
        if ($advancedCustom->ShowAllVideosOnCategory) {
            $total = $value['fullTotal'];
        } else {
            $total = $value['total'];
        }
        if (empty($total)) {
            continue;
        }
        if (in_array($value['id'], $parsed_cats)) {
            continue;
        }
        ?>
        <li>
            <a href="<?php echo $global['webSiteRootURL'] . 'cat/' . $value['clean_name']; ?>" 
               class="<?php echo ($value['clean_name'] == @$_GET['catName'] ? "active" : ""); ?>">
                <?php
                echo '<i class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></i>  ' . __($value['name']);
                if (empty($advancedCustom->hideCategoryVideosCount)) {
                    echo ' <span class="badge">' . $total . '</span>';
                }
                ?>
            </a>
        </li>
        <?php
    }
    $_REQUEST['rowCount'] = $_rowCount;
    ?>
</ul>
</center>
<script>
    var tabsCategoryDocumentHeight = 0;
    $(document).ready(function () {
        tabsCategoryDocumentHeight = $(document).height();
        $('.nav-tabs-horizontal').scrollingTabs();
        //$('.nav-tabs-horizontal').fadeIn();
        setInterval(function () {
            if (tabsCategoryDocumentHeightChanged()) {
                $('.nav-tabs-horizontal').scrollingTabs('refresh');
            }
        }, 1000);
    });

    function tabsCategoryDocumentHeightChanged() {
        var newHeight = $(document).height();
        if (tabsCategoryDocumentHeight !== newHeight) {
            tabsCategoryDocumentHeight = newHeight;
            return true;
        }
        return false;
    }

</script>