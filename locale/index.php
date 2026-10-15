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

require_once '../videos/configuration.php';
require_once '../objects/functions.php';

if (!User::isAdmin() || !empty($global['disableAdvancedConfigurations'])) {
    forbiddenPage('');
}

$localeTexts = [
    'translated' => __('translated', true),
    'missing' => __('missing', true),
    'source' => __('Source language: lists every text, translated to itself', true),
    'addSource' => __('Add the missing texts (no AI needed)', true),
    'overrides' => __('Only the differences from', true),
    'translateAI' => __('Translate with AI', true),
    'edit' => __('Edit', true),
    'sort' => __('Sort', true),
    'stop' => __('Stop', true),
    'stopping' => __('Stopping after the current request...', true),
    'textsFound' => __('texts found in the code', true),
    'aiReady' => __('AI translation is ready. Model:', true),
    'aiMissing' => __('AI translation is not configured. Add this line to videos/configuration.php (or set the ANTHROPIC_API_KEY environment variable):', true),
    'done' => __('Done', true),
    'rejected' => __('translations were rejected because they changed placeholders or HTML; they stay missing', true),
    'saved' => __('Saved', true),
    'added' => __('added', true),
    'updated' => __('updated', true),
    'nothingToSave' => __('Nothing to save', true),
    'confirmAll' => __('Translate every missing text of every language with AI? This can take a while.', true),
    'confirmSortAll' => __('Sort every language file and remove repeated texts?', true),
    'multiline' => __('Texts with line breaks are not listed here; translate them with AI.', true),
    'everythingTranslated' => __('Everything is translated', true),
    'linesMismatch' => __('The number of lines in both columns must be the same', true),
    'filesWithErrors' => __('This file has lines that could not be read; fix it by hand', true),
];
$_page = new Page(array('Translate AVideo'));
?>
<div class="container-fluid">
    <br>
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <h3 class="panel-title pull-left"><i class="fas fa-language"></i> <?php echo __("Translations"); ?></h3>
            <button class="btn btn-default btn-xs pull-right" id="btnRescan"><i class="fas fa-sync"></i> <?php echo __("Scan the code again"); ?></button>
        </div>
        <div class="panel-body">
            <div id="aiStatus" class="alert alert-info"></div>
            <p><strong id="stringsCount">0</strong> <?php echo __("texts found in the code"); ?>.
                <?php echo __("Every file is saved sorted alphabetically, with each text only once, and is validated before it replaces the old one."); ?></p>
            <div class="row">
                <div class="col-sm-4">
                    <button class="btn btn-primary btn-block" id="btnTranslateAll" disabled><i class="fas fa-robot"></i> <?php echo __("Translate everything missing with AI"); ?></button>
                </div>
                <div class="col-sm-4">
                    <button class="btn btn-danger btn-block hidden" id="btnStop"><i class="fas fa-stop"></i> <?php echo __("Stop"); ?></button>
                </div>
                <div class="col-sm-4">
                    <button class="btn btn-default btn-block" id="btnSortAll"><i class="fas fa-sort-alpha-down"></i> <?php echo __("Sort all files and remove repeated texts"); ?></button>
                </div>
            </div>
            <p class="help-block" id="runStatus"></p>
            <div class="table-responsive">
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><?php echo __("Language"); ?></th>
                            <th><?php echo __("Progress"); ?></th>
                            <th class="text-right"><?php echo __("Missing"); ?></th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody id="languagesTable"></tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-8">
                    <select class="form-control" id="newLanguage"></select>
                </div>
                <div class="col-sm-4">
                    <button class="btn btn-default btn-block" id="btnAddLanguage" disabled><i class="fas fa-plus"></i> <?php echo __("Add language and translate with AI"); ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default hidden" id="editorPanel">
        <div class="panel-heading clearfix">
            <h3 class="panel-title pull-left"><i class="fas fa-edit"></i> <?php echo __("Edit translations"); ?>: <span id="editorLanguage"></span></h3>
            <label class="pull-right"><input type="checkbox" id="editorShowAll"> <?php echo __("Show all texts, not only the missing ones"); ?></label>
        </div>
        <div class="panel-body">
            <p class="help-block"><?php echo __("Copy the original texts, translate them (one per line, in the same order) and paste them in the second column. Empty lines are ignored."); ?> <span id="editorMultiline"></span></p>
            <div class="row">
                <div class="col-md-6">
                    <h4><?php echo __("Original texts"); ?></h4>
                    <textarea class="form-control" rows="20" readonly="readonly" id="originalWords" wrap="off"></textarea>
                </div>
                <div class="col-md-6">
                    <h4><?php echo __("Translations"); ?></h4>
                    <textarea class="form-control" rows="20" id="translatedWords" wrap="off" placeholder="<?php echo __("Paste here the translated words, one each line"); ?>"></textarea>
                </div>
            </div>
            <br>
            <button class="btn btn-success btn-block" id="btnSaveTranslations"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
        </div>
    </div>
</div>
<script>
    var localeEndpoint = webSiteRootURL + 'objects/localeTranslator.json.php';
    var localeSaveToken = <?php echo json_encode(getToken()); ?>;
    var localeTexts = <?php echo json_encode($localeTexts, JSON_UNESCAPED_UNICODE); ?>;
    var localeLanguages = [];
    var localeRunning = false;
    var localeStopRequested = false;
    var localeEditorLang = '';
    var localeEditorItems = [];

    function localePost(data) {
        data.globalToken = localeSaveToken;
        return $.ajax({url: localeEndpoint, type: 'POST', data: data, dataType: 'json'});
    }

    function localeLoadStatus(rescan) {
        modal.showPleaseWait();
        return $.ajax({url: localeEndpoint, data: {action: 'status', rescan: rescan ? 1 : 0}, dataType: 'json'}).done(function (response) {
            if (response.error) {
                avideoAlertError(response.msg);
                return;
            }
            localeLanguages = response.languages;
            $('#stringsCount').text(response.strings);
            var status = $('#aiStatus').empty();
            if (response.ai.configured) {
                status.removeClass('alert-warning').addClass('alert-info').text(localeTexts.aiReady + ' ' + response.ai.model);
            } else {
                status.removeClass('alert-info').addClass('alert-warning').text(localeTexts.aiMissing);
                status.append($('<pre>').text("$global['localeTranslatorApiKey'] = 'sk-ant-...';"));
            }
            $('#btnTranslateAll, #btnAddLanguage').prop('disabled', !response.ai.configured || localeRunning);
            var select = $('#newLanguage').empty();
            $.each(response.newLanguages, function (i, language) {
                select.append($('<option>').val(language.code).text(language.name + ' (' + language.code + ')'));
            });
            localeRenderTable(response.ai.configured);
        }).fail(function () {
            avideoToastError('Request failed');
        }).always(function () {
            modal.hidePleaseWait();
        });
    }

    function localeRenderTable(aiConfigured) {
        var table = $('#languagesTable').empty();
        $.each(localeLanguages, function (i, language) {
            var total = language.translated + language.missing;
            var percent = total ? Math.floor(language.translated * 100 / total) : 100;
            var row = $('<tr>').attr('data-lang', language.code);
            var name = $('<td>').append($('<strong>').text(language.name)).append(' ').append($('<small class="text-muted">').text(language.code));
            if (language.base) {
                name.append($('<br>')).append($('<small class="text-muted">').text(localeTexts.overrides + ' ' + language.base));
            }
            if (language.code === 'en_US') {
                name.append($('<br>')).append($('<small class="text-muted">').text(localeTexts.source));
            }
            if (language.errors.length) {
                name.append($('<br>')).append($('<small class="text-danger">').text(localeTexts.filesWithErrors + ': ' + language.errors.join('; ')));
            }
            var bar = $('<div class="progress-bar progress-bar-success">').css('width', percent + '%').text(percent + '%');
            var actions = $('<td class="text-right text-nowrap">');
            if (language.canTranslate && aiConfigured) {
                actions.append($('<button class="btn btn-primary btn-xs localeTranslate">').attr('title', localeTexts.translateAI).html('<i class="fas fa-robot"></i>').prop('disabled', !language.missing || localeRunning));
            }
            if (language.code === 'en_US' && language.missing) {
                actions.append($('<button class="btn btn-primary btn-xs localeSource">').attr('title', localeTexts.addSource).html('<i class="fas fa-plus"></i>').prop('disabled', localeRunning));
            }
            if (!language.errors.length) {
                actions.append(' ').append($('<button class="btn btn-default btn-xs localeEdit">').attr('title', localeTexts.edit).html('<i class="fas fa-edit"></i>'));
                actions.append(' ').append($('<button class="btn btn-default btn-xs localeSort">').attr('title', localeTexts.sort).html('<i class="fas fa-sort-alpha-down"></i>'));
            }
            row.append(name)
                .append($('<td>').append($('<div class="progress">').append(bar)))
                .append($('<td class="text-right localeMissing">').text(language.missing))
                .append(actions);
            table.append(row);
        });
    }

    function localeSetRunning(running) {
        localeRunning = running;
        localeStopRequested = false;
        $('#btnStop').toggleClass('hidden', !running);
        $('#btnTranslateAll, #btnAddLanguage, #btnSortAll, #btnRescan, .localeTranslate, .localeSort, .localeSource').prop('disabled', running);
    }

    // Translates the missing texts of one language, one batch per request, until none is left.
    function localeTranslateLanguage(lang) {
        var deferred = $.Deferred();
        var offset = 0;
        var rejected = 0;
        var translated = 0;
        function next() {
            if (localeStopRequested) {
                deferred.resolve(translated, rejected);
                return;
            }
            $('#runStatus').text(lang + ': ' + translated + ' ' + localeTexts.translated + '...');
            localePost({action: 'translate', lang: lang, offset: offset}).done(function (response) {
                if (response.error) {
                    avideoAlertError(lang + ': ' + response.msg);
                    deferred.reject();
                    return;
                }
                translated += response.translated;
                rejected += response.rejectedCount;
                offset += response.rejectedCount;
                var row = $('#languagesTable tr[data-lang="' + lang + '"]');
                var done = response.total - response.missing;
                var percent = response.total ? Math.floor(done * 100 / response.total) : 100;
                row.find('.progress-bar').css('width', percent + '%').text(percent + '%');
                row.find('.localeMissing').text(response.missing);
                if (response.missing <= offset || (response.translated === 0 && response.rejectedCount === 0)) {
                    deferred.resolve(translated, rejected);
                    return;
                }
                next();
            }).fail(function () {
                avideoToastError(lang + ': request failed');
                deferred.reject();
            });
        }
        next();
        return deferred.promise();
    }

    function localeTranslateQueue(langs) {
        localeSetRunning(true);
        var totalRejected = 0;
        function next(index) {
            if (index >= langs.length || localeStopRequested) {
                localeSetRunning(false);
                $('#runStatus').text(localeTexts.done + (totalRejected ? ' - ' + totalRejected + ' ' + localeTexts.rejected : ''));
                localeLoadStatus(false);
                return;
            }
            localeTranslateLanguage(langs[index]).done(function (translated, rejected) {
                totalRejected += rejected;
                next(index + 1);
            }).fail(function () {
                localeSetRunning(false);
                localeLoadStatus(false);
            });
        }
        // en_US first receives every text (translated to itself): the other languages follow its keys.
        localePost({action: 'source'}).done(function (response) {
            if (response.error) {
                avideoAlertError(response.msg);
                localeSetRunning(false);
                return;
            }
            next(0);
        }).fail(function () {
            avideoToastError('Request failed');
            localeSetRunning(false);
        });
    }

    function localeOpenEditor(lang) {
        localeEditorLang = lang;
        modal.showPleaseWait();
        $.ajax({url: localeEndpoint, data: {action: 'strings', lang: lang, mode: $('#editorShowAll').is(':checked') ? 'all' : 'missing'}, dataType: 'json'}).done(function (response) {
            if (response.error) {
                avideoAlertError(response.msg);
                return;
            }
            var multiline = 0;
            localeEditorItems = [];
            $.each(response.items, function (i, item) {
                if (/[\r\n]/.test(item[0]) || /[\r\n]/.test(item[1])) {
                    multiline++;
                    return;
                }
                localeEditorItems.push(item);
            });
            $('#editorLanguage').text(lang);
            $('#editorMultiline').text(multiline ? localeTexts.multiline : '');
            $('#originalWords').val(localeEditorItems.map(function (item) { return item[0]; }).join('\n'));
            $('#translatedWords').val(localeEditorItems.map(function (item) { return item[1]; }).join('\n'));
            $('#editorPanel').removeClass('hidden');
            if (!localeEditorItems.length) {
                avideoToastSuccess(localeTexts.everythingTranslated);
            }
            $('html, body').animate({scrollTop: $('#editorPanel').offset().top}, 300);
        }).always(function () {
            modal.hidePleaseWait();
        });
    }

    $(document).ready(function () {
        localeLoadStatus(false);

        $('#btnRescan').click(function () {
            localeLoadStatus(true);
        });

        $('#btnStop').click(function () {
            localeStopRequested = true;
            $('#runStatus').text(localeTexts.stopping);
        });

        $('#btnTranslateAll').click(async function () {
            if (!await avideoConfirm(localeTexts.confirmAll)) {
                return;
            }
            var langs = [];
            $.each(localeLanguages, function (i, language) {
                if (language.canTranslate && language.missing) {
                    langs.push(language.code);
                }
            });
            localeTranslateQueue(langs);
        });

        $('#btnAddLanguage').click(function () {
            var lang = $('#newLanguage').val();
            if (lang) {
                localeTranslateQueue([lang]);
            }
        });

        $(document).on('click', '.localeTranslate', function () {
            localeTranslateQueue([$(this).closest('tr').data('lang')]);
        });

        $(document).on('click', '.localeSource', function () {
            modal.showPleaseWait();
            localePost({action: 'source'}).done(function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                    return;
                }
                avideoToastSuccess(localeTexts.saved + ': ' + response.added + ' ' + localeTexts.added);
                localeLoadStatus(false);
            }).fail(function () {
                avideoToastError('Request failed');
            }).always(function () {
                modal.hidePleaseWait();
            });
        });

        $(document).on('click', '.localeEdit', function () {
            localeOpenEditor($(this).closest('tr').data('lang'));
        });

        $('#editorShowAll').change(function () {
            if (localeEditorLang) {
                localeOpenEditor(localeEditorLang);
            }
        });

        function localeNormalize(lang) {
            modal.showPleaseWait();
            localePost({action: 'normalize', lang: lang}).done(function (response) {
                avideoResponse(response);
                if (!response.error) {
                    localeLoadStatus(false);
                }
            }).fail(function () {
                avideoToastError('Request failed');
            }).always(function () {
                modal.hidePleaseWait();
            });
        }

        $(document).on('click', '.localeSort', function () {
            localeNormalize($(this).closest('tr').data('lang'));
        });

        $('#btnSortAll').click(async function () {
            if (await avideoConfirm(localeTexts.confirmSortAll)) {
                localeNormalize('all');
            }
        });

        $('#originalWords').scroll(function () {
            $('#translatedWords').scrollTop($(this).scrollTop());
        });
        $('#translatedWords').scroll(function () {
            $('#originalWords').scrollTop($(this).scrollTop());
        });

        $('#btnSaveTranslations').click(function () {
            var lines = $('#translatedWords').val().split('\n');
            if (lines.length !== localeEditorItems.length && !(lines.length === 1 && lines[0] === '' && localeEditorItems.length === 0)) {
                avideoAlertError(localeTexts.linesMismatch);
                return;
            }
            var pairs = [];
            $.each(localeEditorItems, function (i, item) {
                var translation = lines[i].replace(/\r$/, '');
                if (translation.trim() !== '' && translation !== item[1]) {
                    pairs.push([item[0], translation]);
                }
            });
            if (!pairs.length) {
                avideoToastWarning(localeTexts.nothingToSave);
                return;
            }
            modal.showPleaseWait();
            localePost({action: 'save', lang: localeEditorLang, translations: JSON.stringify(pairs)}).done(function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                    return;
                }
                var msg = localeTexts.saved + ': ' + response.added + ' ' + localeTexts.added + ', ' + response.updated + ' ' + localeTexts.updated;
                if (response.rejectedCount) {
                    avideoAlertError(msg + '. ' + response.rejectedCount + ' ' + localeTexts.rejected + ':\n' + Object.keys(response.rejected).join('\n'));
                } else {
                    avideoToastSuccess(msg);
                }
                localeLoadStatus(false);
                localeOpenEditor(localeEditorLang);
            }).fail(function () {
                avideoToastError('Request failed');
            }).always(function () {
                modal.hidePleaseWait();
            });
        });
    });
</script>
<?php
$_page->print();
?>
