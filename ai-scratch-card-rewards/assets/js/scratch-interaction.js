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
        var prizeType = ($hidden.data('prize-type') || '').toString().toLowerCase();
        var prizeImage = ($hidden.data('prize-image') || '').toString();
        var hasRevealed = false;

        var showPrize = function(prize, result) {
               if (hasRevealed) {
                return;
            }

            hasRevealed = true;
            $hidden.addClass('is-visible').attr('aria-hidden', 'false');

            if (localized) {
                // Send AJAX to log result
                $.ajax({
                    url: localized.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'aiscratch_submit_result',
                        nonce: localized.nonce,
                        card_id: cardId,
                        result: result,
                        prize: prize
                    }
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
hasRevealed = false;
            $hidden.removeClass('is-visible').attr('aria-hidden', 'true');

            var prizeValue = $hidden.data('prize-value') || '';
            var defaultResult = $hidden.data('default-result') || 'lose';

            var scratchOptions = {
                size: 50,
                 scratchMove: function(e, percent) {
                    if (hasRevealed) {
                        return;
                    }

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
                scratchOptions.fg = coverImage;
            } else {
                scratchOptions.fg = surfaceColor;
            }

            if (prizeType === 'image' && prizeImage) {
                scratchOptions.bg = prizeImage;
            } else {
                scratchOptions.bg = '#ffffff';
            }

            // Attach wScratchPad
            if (typeof $canvas.wScratchPad === 'function') {
                $canvas.wScratchPad(scratchOptions);
            } else {
                // eslint-disable-next-line no-console
                console.error('Scratch library missing.');
            }
        };

        // If lead capture is enabled
        if ($leadForm.length > 0 && $leadForm.is(':visible')) {
            $leadForm.show();
           var $submitButton = $leadForm.find('.aiscratch-submit-lead');

            $submitButton.on('click', function() {
                if (!localized) {
                    alert('Unable to start the scratch card at the moment.');
                    return;
                }

                var name = $leadForm.find('.aiscratch-name').val();
                var email = $leadForm.find('.aiscratch-email').val();
                var consent = $leadForm.find('.aiscratch-consent').is(':checked');

                if (!name || !email || !consent) {
                    alert('Please fill in all fields and accept consent.');
                    return;
                }

                             $submitButton.prop('disabled', true);

                $.ajax({
                    url: localized.ajax_url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'aiscratch_submit_lead',
                        nonce: localized.nonce,
                        card_id: cardId,
                        name: name,
                        email: email,
                        consent: consent ? 1 : 0
                    }
                }).done(function(res) {
                    if (res && res.success) {
                        $leadForm.hide();
                        initScratch();
                    } else if (res && res.data && res.data.message) {
                        alert(res.data.message);
                    } else {
                        alert('Unable to save your details. Please try again.');
                    }
                }).fail(function() {
                    alert('Unable to save your details. Please try again.');
                }).always(function() {
                    $submitButton.prop('disabled', false);
                });
            });
        } else {
            // No lead capture
            initScratch();
        }
    });
});
