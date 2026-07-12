(function ($) {

    'use strict';

    $(document).ready(function () {

        $(document).on('click', '.flipnzee-watchlist-button', function (e) {

            e.preventDefault();

            const button = $(this);

            const auctionId = button.data('auction-id');

            const isWatchlisted = button.hasClass('watchlisted');

            const ajaxAction = isWatchlisted
                ? 'flipnzee_remove_from_watchlist'
                : 'flipnzee_add_to_watchlist';

            $.post(
                flipnzeeWatchlist.ajaxUrl,
                {
                    action: ajaxAction,
                    auction_id: auctionId,
                    nonce: flipnzeeWatchlist.nonce
                },
                function (response) {

                    console.log('AJAX Response:', response);

                    if (!response.success) {

                        console.error(response);

                        alert(response.data.message);

                        return;

                    }
                    jQuery.post(
    flipnzeeWatchlist.ajaxUrl,
    {
        action: 'flipnzee_get_watchlist'
    },
    function (response) {

        if (response.success) {

            jQuery('#flipnzee-watchlist-container').html(
                response.data.html
            );

        }

    }
);

                    if (ajaxAction === 'flipnzee_add_to_watchlist') {

                        button
                            .addClass('watchlisted')
                            .text('❤ Remove from Watchlist');

                    } else {

                        button
                            .removeClass('watchlisted')
                            .text('❤ Add to Watchlist');

                    }

                }
            );

        });

    });

})(jQuery);