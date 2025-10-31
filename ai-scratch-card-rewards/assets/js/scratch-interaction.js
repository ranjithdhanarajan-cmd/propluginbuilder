jQuery(document).ready(function($) {
    $('.aiscratch-card-container').each(function() {
        var $container = $(this);
        var cardId = $container.data('card-id');
        var $canvas = $container.find('.aiscratch-canvas');
        var $hidden = $container.find('.aiscratch-hidden-content');
        var $leadForm = $container.find('.aiscratch-lead-form');
       var localized = typeof AISCRATCH !== 'undefined' ? AISCRATCH : null;

        var surfaceColor = $canvas.data('surface-color') || '#999999';
        var coverImage = $canvas.data('cover-image') || '';

        var showPrize = function(prize, result) {
            $hidden.fadeIn();

             if (localized) {
                // Send AJAX to log result
                $.post(localized.ajax_url, {
                    action: 'aiscratch_submit_result',
                    nonce: localized.nonce,
                    card_id: cardId,
                    result: result,
                    prize: prize
                }).fail(function() {
                    // eslint-disable-next-line no-console
                    console.error('Failed to record scratch result.');
                });
            }

            // Confetti on win
            if (result === 'win' && typeof confetti === 'function') {
                confetti();
            }
        };

        var initScratch = function() {
 var prizeValue = $hidden.data('prize-value') || '';
            var defaultResult = $hidden.data('default-result') || 'lose';

            var scratchOptions = {
                size: 50,
        fg: surfaceColor,
        scratchMove: function(e, percent) {
                    if (percent > 50) {
        var result = defaultResult;
                        var displayValue = prizeValue;

            if ($hidden.find('.aiscratch-prize-img').length) {
                            displayValue = $hidden.find('.aiscratch-prize-img').attr('src');
                        }

                        showPrize(displayValue, result);
                        $canvas.wScratchPad('clear');
                    }
                }
                };

            if (coverImage) {
                scratchOptions.bg = coverImage;
            }

            // Attach wScratchPad
            $canvas.wScratchPad(scratchOptions);
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

            if (localized) {
                    $.post(localized.ajax_url, {
                        action: 'aiscratch_submit_lead',
                        nonce: localized.nonce,
                        card_id: cardId,
                        name: name,
                        email: email,
                        consent: consent ? 1 : 0
                    }, function(res) {
                        if (res && res.success) {
                            $leadForm.hide();
                            initScratch();
                        } else if (res && res.data && res.data.message) {
                            alert(res.data.message);
                        }
                    }).fail(function() {
                        alert('Unable to save your details. Please try again.');
                    });
                }
            });
        } else {
            // No lead capture
            initScratch();
        }
    });
});                                            
