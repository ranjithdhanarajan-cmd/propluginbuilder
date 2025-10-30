jQuery(document).ready(function($) {
    $('.aiscratch-card-container').each(function() {
        var $container = $(this);
        var cardId = $container.data('card-id');
        var $canvas = $container.find('.aiscratch-canvas');
        var $hidden = $container.find('.aiscratch-hidden-content');
        var $leadForm = $container.find('.aiscratch-lead-form');

        var showPrize = function(prize, result) {
            $hidden.fadeIn();

            // Send AJAX to log result
            $.post(AISCRATCH.ajax_url, {
                action: 'aiscratch_submit_result',
                nonce: AISCRATCH.nonce,
                card_id: cardId,
                result: result,
                prize: prize
            });

            // Confetti on win
            if (result === 'win' && typeof confetti === 'function') {
                confetti();
            }
        };

        var initScratch = function() {
            // Attach wScratchPad
            $canvas.wScratchPad({
                size: 50,
                fg: 'gray', // scratch surface color or image
                bg: '',     // background image if needed
                scratchMove: function(e, percent) {
                    if (percent > 50) {
                        var prizeText = $hidden.text().trim();
                        var isWin = !$hidden.find('.aiscratch-lose').length;

                        showPrize(prizeText, isWin ? 'win' : 'lose');
                        $canvas.wScratchPad('clear');
                    }
                }
            });
        };

        // If lead capture is enabled
        if ($leadForm.length > 0 && $leadForm.is(':visible')) {
            $leadForm.show();
            $leadForm.find('.aiscratch-submit-lead').on('click', function() {
                var name = $leadForm.find('.aiscratch-name').val();
                var email = $leadForm.find('.aiscratch-email').val();
                var consent = $leadForm.find('.aiscratch-consent').is(':checked');

                if (!name || !email || !consent) {
                    alert('Please fill in all fields and accept consent.');
                    return;
                }

                $.post(AISCRATCH.ajax_url, {
                    action: 'aiscratch_submit_lead',
                    nonce: AISCRATCH.nonce,
                    card_id: cardId,
                    name: name,
                    email: email,
                    consent: consent ? 1 : 0
                }, function(res) {
                    if (res.success) {
                        $leadForm.hide();
                        initScratch();
                    }
                });
            });
        } else {
            // No lead capture
            initScratch();
        }
    });
});
