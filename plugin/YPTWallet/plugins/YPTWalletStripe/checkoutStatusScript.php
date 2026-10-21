    var checkoutStage<?php echo $uid; ?> = '';
    function checkoutStatus<?php echo $uid; ?>(message, stage, title) {
        var checkout = $(form<?php echo $uid; ?>);
        var status = $('#checkout-status<?php echo $uid; ?>');
        status.removeClass('hidden alert-info alert-success alert-danger')
            .addClass(stage === 'error' ? 'alert-danger' : (stage === 'ready' ? 'alert-success' : 'alert-info'));
        $('#checkout-title<?php echo $uid; ?>').text(title);
        $('#payment-status<?php echo $uid; ?>').text(message);
        checkout.find('[data-checkout-step]').removeClass('text-primary text-success');
        var step = stage === 'ready' ? 3 : (stage === 'activating' ? 2 : 1);
        checkout.find('[data-checkout-step="' + step + '"]').addClass(stage === 'ready' ? 'text-success' : 'text-primary');
        status.attr('aria-busy', stage === 'processing' || stage === 'activating' ? 'true' : 'false');
        if (stage === 'activating' || stage === 'ready') {
            checkout.attr('data-checkout-paid', 'true');
        }
        if (checkoutStage<?php echo $uid; ?> !== stage && ['activating', 'ready', 'error', 'pending'].indexOf(stage) !== -1) {
            var statusElement = document.getElementById('checkout-status<?php echo $uid; ?>');
            if (statusElement && typeof statusElement.focus === 'function') { statusElement.focus(); }
        }
        checkoutStage<?php echo $uid; ?> = stage;
    }
