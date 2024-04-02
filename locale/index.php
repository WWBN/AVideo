<?php
if (isset($_GET['getLanguage'])) {
    $lngFile = './' . strtolower(str_replace(['.', '/', '\\'], '', $_GET['getLanguage'])) . '.php';
    if (!file_exists($lngFile)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    require_once($lngFile);
    foreach ($t as $key => $value) {
        if (empty($value)) {
            $t[$key] = $key;
        }
    }

    if (!empty($_REQUEST['print'])) {
        header('Content-Type: text/plain');
        foreach ($t as $key => $value) {
            echo $value . PHP_EOL;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode($t);
    }
    exit;
}

$vars = [];
require_once '../videos/configuration.php';
require_once '../objects/functions.php';

if (!User::isAdmin() || !empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('');
}

$vars = listAllWordsToTranslate();
$_page = new Page(array('Translate AVideo'));
?>
<style type="text/css">
    textarea.form-control {
        height: 100% !important;
    }
</style>
<div class="container-fluid">
    <br>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-sm-8">
                    <button class="btn btn-success btn-block " id="btnSaveFile" disabled><i class="fas fa-save"></i> <?php echo __("Save File"); ?></button>
                </div>
                <div class="col-sm-4">
                    <div class="navbar-lang-btn">
                        <?php
                        if ($lang == 'en') {
                            $lang = 'en_US';
                        }
                        echo Layout::getLangsSelect('selectFlag', $lang, 'selectFlag', 'btn-block', true);
                        //var_dump($lang);exit;
                        ?>
                    </div>
                    <script>
                        $(function() {
                            $("#div_selectFlag a").click(function(event) {
                                event.preventDefault();
                                var value = $(this).attr('value');
                                var tb1 = $('#originalWords');
                                var tb2 = $('#translatedCode');
                                var tb3 = $('#arrayCode');
                                console.log('Changed language');
                                console.log(value);
                                $.ajax({
                                    url: 'index.php?getLanguage=' + value,
                                    dataType: 'json'
                                }).done(function(data) {
                                    console.log("Found existing translation!");
                                    var arrayOfLines = $('#originalWords').val().split('\n');
                                    $('#translatedCode').empty();
                                    $.each(arrayOfLines, function(index, item) {
                                        if (data.hasOwnProperty(item)) {
                                            $('#translatedCode').append(data[item] + '\n');
                                        } else {
                                            $('#translatedCode').append('\n');
                                        }
                                    });
                                    $('#translatedCode').trigger('keyup');
                                    tb1.scroll(function() {
                                        tb2.scrollTop(tb1.scrollTop());
                                    });

                                    tb2.scroll(function() {
                                        tb3.scrollTop(tb2.scrollTop());
                                    });
                                }).fail(function() {
                                    console.log("New translation");
                                    tb1.scroll(function() {

                                    });
                                });
                            });
                        });
                    </script>
                </div>
            </div>

        </div>
        <div class="panel-body">

            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Original words found"); ?></h3>
                    <textarea placeholder="<?php echo __("Original words found"); ?>" class="form-control" rows="20" readonly="readonly" id="originalWords" wrap="off"><?php
                                                                                                                                                                        foreach ($vars as $value) {
                                                                                                                                                                            echo $value, "\n";
                                                                                                                                                                        }
                                                                                                                                                                        ?></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Word Translations"); ?></h3>
                    <textarea placeholder="<?php echo __("Paste here the translated words, one each line"); ?>" class="form-control" id="translatedCode" rows="20" wrap="off"></textarea>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h3><?php echo __("Translated Array"); ?></h3>

                    <textarea placeholder="<?php echo __("Translated Array"); ?>" class="form-control" id="arrayCode" rows="20" readonly="readonly" style="white-space: pre;overflow-wrap: normal;overflow-x: scroll;"></textarea>
                </div>

            </div>
            <?php
            $dir = "{$global['systemRootPath']}locale";
            if (!is_writable($dir)) {
            ?>
                <div class="alert alert-info">
                    <?php echo __("You need to make your locale folder writable"); ?>
                    <pre><code>chown www-data:www-data <?php echo $global['systemRootPath']; ?>locale && sudo chmod -R 755 <?php echo $global['systemRootPath']; ?>locale</code></pre>
                </div>
            <?php
            }
            ?>
            <div class="alert alert-info">
                <?php echo count($vars) . ' words found'; ?>
            </div>
        </div>
    </div>
</div>
<script>
    var arrayLocale = <?php echo json_encode(array_values($vars)); ?>;
    $(document).ready(function() {
        $('#translatedCode').keyup(function() {
            var lines = $(this).val().split('\n');
            console.log(lines);
            if (lines.length > 0 && !(lines.length == 1 && lines[0] === "")) {
                var str = "";
                for (var i = 0; i < lines.length; i++) {
                    if (typeof arrayLocale[i] == "undefined") {
                        break;
                    }
                    var key = arrayLocale[i].replace(/'/g, "\\'");
                    str += "$t['" + key + "'] = \"" + lines[i] + "\";\n";
                }
                $('#arrayCode').val(str);
                $('button').prop('disabled', false);
            } else {
                $('#arrayCode').val("");
                $('button').prop('disabled', true);
            }
        });

        $('#btnSaveFile').click(function() {
            if ($('#btnSaveFile').is(":disabled")) {
                return false;
            }
            modal.showPleaseWait();
            $.ajax({
                url: 'save.php',
                data: {
                    "flag": $("#selectFlag").val(),
                    "code": $('#arrayCode').val()
                },
                type: 'post',
                success: function(response) {
                    if (response.status === "1") {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your language has been saved!"); ?>", "success");
                    } else {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                    }
                    modal.hidePleaseWait();
                }
            });
        });
    });
</script>
<?php
$_page->print();
?>