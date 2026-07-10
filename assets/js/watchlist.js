/**
 * Flipnzee Auctions Watchlist
 *
 * Handles AJAX watchlist requests.
 */

(function ($) {

	'use strict';

	$(document).ready(function () {

		console.log('Flipnzee Watchlist JS Loaded');

		$(document).on(
			'click',
			'.flipnzee-watchlist-button',
			function (e) {

				e.preventDefault();

				const button = $(this);

				const auctionId = button.data('auction-id');

				console.log('Clicked auction:', auctionId);

				$.post(
					flipnzeeWatchlist.ajaxUrl,
					{
						action: 'flipnzee_add_to_watchlist',
						auction_id: auctionId,
						nonce: flipnzeeWatchlist.nonce
					},
					function (response) {

						console.log(response);

					}
				);

			}
		);

	});

})(jQuery);