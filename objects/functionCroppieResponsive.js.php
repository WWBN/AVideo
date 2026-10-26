<script>
    var uploadCrop<?php echo $uid; ?>;
    (function () {
        var crop = $('#croppie<?php echo $uid; ?>');
        var input = $('#upload<?php echo $uid; ?>');
        var imageURL = '';
        var revision = 0;
        var fileRevision = 0;
        var lastWidth = 0;
        var resizeTimer;
        var selected = false;
        var initialized = false;

        function notify(name) {
            crop.trigger('image-editor:' + name, [selected]);
        }
        function bind(url, points) {
            var currentRevision = ++revision;
            notify('loading');
            return crop.croppie('bind', {url: url, points: points}).then(function () {
                if (currentRevision === revision) { notify('ready'); }
            }).catch(function () {
                if (currentRevision === revision) { notify('error'); }
            });
        }
        function create(points) {
            var available = Math.floor(crop.parent().width());
            if (!crop.is(':visible') || available < 1 || !document.contains(crop[0])) { return; }
            var width = Math.min(<?php echo (int) $viewportWidth; ?>, available - 2);
            var height = Math.round(width * <?php echo (float) ($viewportHeight / $viewportWidth); ?>);
            if (initialized) { crop.croppie('destroy'); }
            crop.css('min-height', height + 40);
            uploadCrop<?php echo $uid; ?> = crop.croppie({
                enforceBoundary: <?php echo json_encode($enforceBoundary); ?>,
                mouseWheelZoom: false,
                viewport: {width: width, height: height},
                boundary: {width: width + 2, height: height + 2}
            });
            initialized = true;
            lastWidth = available;
            bind(imageURL, points);
        }
        window.restartCroppie<?php echo $uid; ?> = function (url, isSelection) {
            ++fileRevision;
            imageURL = url;
            selected = isSelection !== false;
            create();
        };
        window.createCroppie<?php echo $uid; ?> = window.restartCroppie<?php echo $uid; ?>;
        crop.on('image-editor:saved', function () { selected = false; });
        $('#upload-btn<?php echo $uid; ?>').on('click', function () {
            if ($(this).attr('aria-disabled') !== 'true') { input.trigger('click'); }
        });
        $('#upload-btn<?php echo $uid; ?>, #library-btn<?php echo $uid; ?>').attr('tabindex', '0').on('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); $(this).trigger('click'); }
        });
        input.on('click', function () { this.value = ''; });
        input.on('change', function () {
            var file = this.files && this.files[0];
            if (!file) { return; }
            var supported = /^image\/(jpeg|pjpeg|jpg|png|webp|gif)$/i.test(file.type) || (!file.type && /\.(jpe?g|png|webp|gif)$/i.test(file.name));
            if (!supported || file.size > 20971520) {
                notify('invalid');
                return;
            }
            var reader = new FileReader();
            var currentFileRevision = ++fileRevision;
            selected = true;
            notify('loading');
            reader.onload = function (event) {
                if (currentFileRevision === fileRevision) { window.restartCroppie<?php echo $uid; ?>(event.target.result); }
            };
            reader.onerror = function () { if (currentFileRevision === fileRevision) { notify('error'); } };
            reader.readAsDataURL(file);
        });
        $('#library-btn<?php echo $uid; ?>').on('click', function () {
            notify('library');
        });
        function resize() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (!imageURL || !crop.is(':visible') || !document.contains(crop[0])) { return; }
                if (Math.abs(crop.parent().width() - lastWidth) > 2) {
                    var points = initialized ? crop.croppie('get').points : undefined;
                    create(points);
                }
            }, 150);
        }
        if (window.ResizeObserver) {
            var observer = new ResizeObserver(resize);
            observer.observe(crop.parent()[0]);
        } else {
            $(window).on('resize', resize);
            $(document).on('shown.bs.tab', resize);
        }
    }());
</script>
