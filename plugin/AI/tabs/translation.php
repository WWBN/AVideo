<?php
$video = new Video('', '', $videos_id);
$filename = $video->getFilename();
//var_dump($filename);exit;
$vttfile = AI::getFirstVTTFile($videos_id);
//echo $vttfile;
$hasTranscriptionFile = file_exists($vttfile);
$columnCallbackFunctions = ['text'];
?>
<style>
    .langButton {
        cursor: pointer;
    }
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <h1>
            <?php echo __('Translate from transcription file'); ?>
        </h1>
        <small>
            If your preferred language isn't available in our transcription/language selection menu, don't worry!
            You can still get your translations in the language you need. Simply choose your desired language from below.
            We'll handle the translation directly from one subtitle file.
        </small>
    </div>
    <div class="panel-heading">
        <input class="form-control" id="searchInput" type="text" placeholder="Search Languages...">
    </div>
    <div class="panel-body" style="max-height: calc(100vh - 300px); overflow: auto;">
        <form id="languagesForm">
        </form>
    </div>
    <div class="panel-footer">
        <?php
        if (!$hasTranscriptionFile) {
        ?>
            <div class="alert alert-warning"><strong>Note:</strong> A transcription is required for translations. Currently, there is no transcription file associated with this video.</div>
        <?php
        } else {
        ?>
            <button class="btn btn-success btn-block" onclick="generateAITranslations()">
                <i class="fa-solid fa-language"></i> <?php echo __('Generate Translations') ?>
                <?php
                if (!empty($priceForTranslation)) {
                    echo "<br><span class=\"label label-success\">{$priceForTranslationText}</span>";
                }
                ?>
            </button>
        <?php
        }
        ?>
    </div>
</div>

<script>
    var hasTranscriptionRecord = false;

    function deleteLang(key) {
        avideoConfirm('Delete This Lang file?').then(response => {
            if (response) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/AI/deleteLang.json.php',
                    data: {
                        key: key,
                        videos_id: <?php echo $videos_id; ?>
                    },
                    type: 'post',
                    success: function(response) {
                        if (response.error) {
                            avideoAlertError(response.msg);
                        } else {
                            console.log(response);
                        }
                        loadLangs();
                        modal.hidePleaseWait();
                    }
                });
            } else {
                return false;
            }
        });

    }

    function sortCheckboxes() {
        var $form = $('#languagesForm');
        var checkedCheckboxes = $form.find('.languageCheckbox:checked').closest('.checkbox').toArray();
        var uncheckedCheckboxes = $form.find('.languageCheckbox:not(:checked)').closest('.checkbox').toArray();

        // Append checked checkboxes at the beginning of the form
        checkedCheckboxes.forEach(function(checkbox) {
            $form.prepend(checkbox);
        });

        // Append unchecked checkboxes after the checked ones
        uncheckedCheckboxes.forEach(function(checkbox) {
            $form.append(checkbox);
        });
    }

    function getTranslationCheckedValues() {
        var checkedValues = $('#languagesForm input[type="checkbox"]:checked').map(function() {
            return {
                code: $(this).data('lang-code'),
                name: $(this).val()
            };
        }).get();

        return (checkedValues);
    }

    async function generateAITranslations() {
        var langArray = getTranslationCheckedValues();
        modalContinueAISuggestions.showPleaseWait();

        for (let langArrayItem of langArray) {
            console.log(langArrayItem);
            try {
                await new Promise((resolve, reject) => {
                    $.ajax({
                        url: webSiteRootURL + 'plugin/AI/async.json.php',
                        data: {
                            videos_id: <?php echo $videos_id; ?>,
                            translation: 1,
                            lang: langArrayItem.code,
                            langName: langArrayItem.name,
                            type: '<?php echo AI::$typeTranslation; ?>'
                        },
                        type: 'post',
                        success: function(response) {
                            if (response.error) {
                                if (response.alert) {
                                    avideoAlertError(response.msg);
                                } else {
                                    avideoToastWarning(response.msg);
                                }
                                reject(response.msg);
                            } else {
                                avideoToast(response.msg);
                                resolve();
                            }

                            var callback = 'loadLangs();';
                        },
                        complete: function(resp) {
                            response = resp.responseJSON
                            console.log(response);
                            modalContinueAISuggestions.hidePleaseWait();
                            if (response.error) {
                                avideoAlertError(response.msg);
                                reject(response.msg);
                            }
                        }
                    });
                });
            } catch (error) {
                console.error("Error in processing language:", langArrayItem, error);
                //break; // If you want to stop processing further languages upon error
            }
        }

        modalContinueAISuggestions.hidePleaseWait();
    }

    function loadLangs() {
        $.ajax({
            url: webSiteRootURL + 'plugin/AI/tabs/langs.php',
            data: {
                videos_id: <?php echo $videos_id; ?>
            },
            type: 'post',
            success: function(response) {
                $('#languagesForm').html(response);
                processLangCheckboxes();
            }
        });
    }


    function processLangCheckboxes() {
        $('.languageCheckbox').on('change', function() {
            var langCode = $(this).data('lang-code');
            var isChecked = $(this).is(':checked');
            Cookies.set('lang_' + langCode, isChecked, {
                path: '/',
                expires: 365
            });
            sortCheckboxes(); // Call the sorting function
        });
        sortCheckboxes();
    }


    $(document).ready(function() {
        // Search filter
        $("#searchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#languagesForm div").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        loadLangs();
    });
</script>