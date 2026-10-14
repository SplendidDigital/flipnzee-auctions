# Flipnzee Auctions

**Version:** 1.12.65  
**Database schema:** 1.9.3  
**Requires WordPress:** 6.5+  
**Requires PHP:** 8.1+  
**Author:** Splendid Digital Solutions  
**License declared in plugin header:** GPL-2.0-or-later  
**Project:** https://flipnzee.com  
**Repository:** https://github.com/SplendidDigital/flipnzee-auctions

Flipnzee Auctions is a WordPress plugin for running auctions of websites, domains, online businesses, and other digital assets. It includes auction listings, bid processing, buyer-facing purchase pages, watchlists, transaction administration, payment-provider abstractions, Escrow.com integration components, transfer tracking, activity logging, and a Buy Now purchase-intent lifecycle.

This README documents the implementation in the supplied **v1.12.65** package, including the Lesson 239 stale Buy Now lock/bid-unblock safeguard.

> **Clicky Analytics note:** This package does **not** implement a Clicky Analytics integration. It contains a small display of cached analytics values when compatible `flipnzee_main_{listing_id}` and `flipnzee_meta_{listing_id}` transients already exist, but it does not register a Clicky API client, authenticate with Clicky, or fetch Clicky data. Those cached values are labelled â€œGoogle Verified Analyticsâ€ and include users, sessions, and Google impressions. Do not describe this as Clicky integration. If Clicky support is intended, it needs a separate, explicit implementation and documentation.

---

## Contents

- [At a glance](#at-a-glance)
- [Core functionality](#core-functionality)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick setup](#quick-setup)
- [Shortcodes](#shortcodes)
- [Auction lifecycle](#auction-lifecycle)
- [Bidding and bidder privacy](#bidding-and-bidder-privacy)
- [Buy Now purchase intents and listing locks](#buy-now-purchase-intents-and-listing-locks)
- [Lesson 239: stale-lock recovery and bid unblocking](#lesson-239-stale-lock-recovery-and-bid-unblocking)
- [Payments and payment providers](#payments-and-payment-providers)
- [Escrow.com integration](#escrowcom-integration)
- [Transactions, purchases, and transfers](#transactions-purchases-and-transfers)
- [Watchlists](#watchlists)
- [Administration](#administration)
- [Database and persistence](#database-and-persistence)
- [Hooks, scheduling, and assets](#hooks-scheduling-and-assets)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Testing and production checklist](#testing-and-production-checklist)
- [Version 1.12.65 notes](#version-11265-notes)
- [Known limitations and integrations not included](#known-limitations-and-integrations-not-included)

---

## At a glance

| Item | Details |
|---|---|
| Plugin | Flipnzee Auctions |
| Plugin version | `1.12.65` |
| Database schema constant | `1.9.3` |
| WordPress minimum | `6.5` |
| PHP minimum | `8.1` |
| Main post type used by auction cards | `listing` (the post must exist) |
| Frontend auction shortcode | `[flipnzee_auctions]` |
| Buyer pages | `[flipnzee_my_purchases]`, `[flipnzee_purchase_details]`, `[flipnzee_payment_page]` |
| Watchlist page | `[flipnzee_watchlist]` |
| Live Buy Now transaction creation | Disabled by default; explicit opt-in constant required |
| Clicky Analytics API integration | Not present in this package |

---

## Core functionality

### Auctions
- Displays active auctions and their associated WordPress listing posts.
- Shows start price, current bid, auction timing/status, and bidder information.
- Shows public bidder labels such as â€œBidder 1â€ and â€œBidder 2â€ rather than exposing bidder usernames in the auction UI.
- Handles bid placement and highest/winning bid resolution through dedicated manager classes.
- Uses scheduled maintenance to process auction lifecycle tasks.
- Keeps recently closed auctions visible for a configured history period.

### Buy Now
- Provides a purchase-intent workflow associated with an auction/listing.
- Uses a shared listing lock to prevent conflicting auction bids and simultaneous Buy Now activity while a valid purchase is in progress.
- Tracks intent states and can link an intent to an external provider transaction.
- Includes administrator tools for reviewing intents and diagnosing/recovering eligible stale locks.
- Requires explicit production authorization before live Buy Now transaction creation is enabled.

### Payments and providers
- Includes a payment manager and provider abstractions.
- Includes Escrow.com API/provider and webhook components.
- Supports manual payment handling and a Stripe-related gateway path in the payment manager.
- Includes transaction state/lifecycle managers and payment resolution helpers.
- Actual gateway availability depends on configuration and the code path used. A provider being represented in code does not by itself mean the account is configured or production-ready.

### Buyer and seller administration
- Buyer-facing purchase list and purchase details shortcodes.
- Payment page shortcode.
- Admin pages for auctions, payments, transactions, transaction details, Buy Now intents, activity log, Escrow settings, and transfer management.
- Transfer tracking is designed for digital-asset ownership transfer workflows; ownership transfer is not automatically completed merely because payment is initiated.

### Other
- Watchlist storage and AJAX handlers.
- Activity logging.
- Database schema migration support.
- Notification manager.
- Frontend and admin styles/scripts.

---

## Requirements

- WordPress 6.5 or newer.
- PHP 8.1 or newer.
- A WordPress user account with appropriate permissions for bidding and purchase-related actions.
- A published WordPress post for each auction's associated listing.
- HTTPS strongly recommended, especially for authentication and payment-provider callbacks.
- For Escrow: a valid Escrow account and API credentials appropriate to the environment, plus verified callback/webhook configuration as required by the deployed integration.
- For Stripe or any other gateway: the provider's own account, keys, and configuration, if that gateway is being used.

---

## Installation

1. Back up the WordPress files and database.
2. Test upgrades on a staging copy first, particularly when existing auctions, bids, transactions, or Buy Now intents exist.
3. In WordPress, open **Plugins â†’ Add New Plugin â†’ Upload Plugin**.
4. Upload the `flipnzee-auctions` ZIP package.
5. Install and activate it.
6. Confirm activation completes without PHP errors.
7. Open the Flipnzee Auctions administration menu and review settings and existing records.
8. Create or verify the WordPress listing posts that auctions will reference.
9. Configure payment and Escrow settings only if those workflows will be used.
10. Create the pages needed for the frontend shortcodes below.
11. Test the complete flow using test accounts and a sandbox environment before enabling production transactions.

### Upgrade precautions
- Take a database backup first.
- Do not delete database tables or options manually as a routine upgrade step.
- Preserve transaction, bid, purchase-intent, and provider-reference records.
- After upgrading, verify existing auction visibility, bid placement, Buy Now lock status, scheduled maintenance, and administrator pages.

---

## Quick setup

### 1. Create a listing
Create a WordPress post of the type expected by the auction manager (`listing`) and publish it. Add a title, useful description, and featured image. An auction references the listing post ID, so deleting or unpublishing the referenced post can affect the frontend card.

### 2. Create/manage an auction
Use the plugin's admin auction controls to create an auction and associate it with the listing. Set the start price, end date/time, and auction status using the fields available in the installed version. Verify timezone interpretation and the end-time behaviour on staging.

### 3. Add the auction page
Create a WordPress page such as **Auctions** and add:

```text
[flipnzee_auctions]
```

### 4. Add buyer pages
Create pages for purchases, purchase details, and payment as appropriate:

```text
[flipnzee_my_purchases]
```

```text
[flipnzee_purchase_details]
```

```text
[flipnzee_payment_page]
```

The plugin's links and transaction workflow must point users to the correct pages. Test with a regular buyer account.

### 5. Add a watchlist page
Create a page such as **My Watchlist**:

```text
[flipnzee_watchlist]
```

Test while logged in and logged out.

### 6. Configure payments carefully
Review the payment gateway and Escrow settings. Do not enable production Buy Now transaction creation until the production workflow, provider account, webhook evidence, and recovery process have been verified.

---

## Shortcodes

The supplied `includes/class-shortcodes.php` registers these five shortcodes.

| Shortcode | Purpose | Attributes |
|---|---|---|
| `[flipnzee_auctions]` | Renders active auction cards with listing information, bids, status, and auction actions | None in the shortcode method |
| `[flipnzee_my_purchases]` | Renders the current buyer's purchase list | None documented in the registration |
| `[flipnzee_purchase_details]` | Renders purchase details through the buyer-details component | None documented in the registration |
| `[flipnzee_payment_page]` | Renders the payment page component | None documented in the registration |
| `[flipnzee_watchlist]` | Renders watchlist UI and logged-in/logged-out states | None in the shortcode method |

### `[flipnzee_auctions]`

**Use:**

```text
[flipnzee_auctions]
```

**Output:** An auction listing interface. The renderer obtains active auctions through `Flipnzee_Auction_Manager::get_active_auctions()`. For each auction with an existing listing post, the card can show:
- Listing featured image or a placeholder.
- Auction status label: **Live Auction**, **Ending Soon**, or **Auction Ended**.
- Listing title.
- Cached analytics summary if the expected analytics transients exist.
- Start price and current bid.
- Anonymous bidder labels and bid-related details.
- Bid submission controls where the auction is eligible.
- Buy Now controls and state notices when the purchase-intent workflow applies.
- Feedback messages for successful or failed bid submission.

If no active auctions are found, it returns a â€œNo active auctions foundâ€ message. Auctions whose referenced listing post no longer exists are skipped.

The shortcode does not accept documented attributes in this version.

### `[flipnzee_my_purchases]`

**Use:**

```text
[flipnzee_my_purchases]
```

**Output:** A buyer-facing purchase list rendered by `Flipnzee_My_Purchases`. Use it on a page intended for logged-in buyers. Confirm access-control and empty-state behaviour with a regular account after installation.

### `[flipnzee_purchase_details]`

**Use:**

```text
[flipnzee_purchase_details]
```

**Output:** A purchase details view rendered by `Flipnzee_My_Purchase_Details`. Use it on the purchase-details page configured by your site workflow. Test direct access to another user's transaction to confirm authorization is enforced.

### `[flipnzee_payment_page]`

**Use:**

```text
[flipnzee_payment_page]
```

**Output:** The payment page rendered by `Flipnzee_Payment_Page`. It is intended to be reached from the transaction/payment workflow. Do not assume that visiting this page alone creates or completes a payment.

### `[flipnzee_watchlist]`

**Use:**

```text
[flipnzee_watchlist]
```

**Output:** The watchlist interface rendered by the shortcode class. The plugin includes a dedicated watchlist manager and AJAX handler. Test adding/removing items and verify that one user's saved items are not visible to another user.

### Suggested page map

| WordPress page | Shortcode |
|---|---|
| Auctions | `[flipnzee_auctions]` |
| My Purchases | `[flipnzee_my_purchases]` |
| Purchase Details | `[flipnzee_purchase_details]` |
| Payment | `[flipnzee_payment_page]` |
| My Watchlist | `[flipnzee_watchlist]` |

The exact page URLs are site-specific. Set up the links in navigation and test the journey from auction card to bid, purchase intent, payment, and purchase details.

---

## Auction lifecycle

At a high level, an auction proceeds through these stages:

1. **Listing exists:** A WordPress `listing` post represents the asset.
2. **Auction is configured:** An auction record references the listing and stores price, timing, and status.
3. **Auction becomes active:** The auction manager makes it available to the frontend shortcode.
4. **Bids are submitted:** The bid manager validates/processes bids according to the current auction state and rules.
5. **Highest/winning bid is resolved:** Bid-manager helpers provide the current highest bidder and winning bid.
6. **Auction ends:** The auction status or end time causes the UI to label it ended; scheduled maintenance handles lifecycle work.
7. **Payment/transaction is managed:** The transaction and payment components manage the relevant transaction state.
8. **Transfer is tracked:** Administrator transfer tools can record ownership-transfer progress for the digital asset.

Do not assume that an auction's displayed end state alone confirms payment, provider settlement, or ownership transfer. Those are separate lifecycle states and must be reconciled with the relevant transaction/provider records.

### Closed-auction history
The main plugin defines:

```php
define( 'FLIPNZEE_AUCTION_HISTORY_DAYS', 10 );
```

This setting controls the number of days recently closed auctions remain visible where that constant is used. It is a code constant, not a shortcode attribute. Changing it requires an intentional code/configuration change and testing.

---

## Bidding and bidder privacy

The auction frontend builds stable anonymous labels from unique WordPress user IDs:
- The first distinct bidder is labelled â€œBidder 1â€.
- The next distinct bidder is labelled â€œBidder 2â€.
- Repeated bids by the same user reuse that user's assigned label within the rendered auction.

This is intended to avoid exposing usernames in the public bidder display. Administrators still need access to the records necessary to operate the marketplace.

The bid handler and frontend both consult the shared Buy Now listing-lock state. When a valid purchase intent has reserved a listing, bids may be blocked to avoid conflicting purchase flows. A stale lock must not continue to block a legitimate active auction; see Lesson 239 below.

Always test:
- A valid bid on an active auction.
- A bid after the auction end time.
- A bid while a valid Buy Now intent is active.
- A bid after a stale lock is safely reconciled.
- Repeated bids by the same bidder and stable anonymous labels.
- Two different bidders and public privacy.

---

## Buy Now purchase intents and listing locks

The Buy Now intent system coordinates a buyer's request with the listing and auction state. A listing-level lock prevents competing purchase or bid actions while a valid reservation is active.

The package includes `Flipnzee_Buy_Now_Intent`, the administrator Buy Now intents screen, transaction-state helpers, Escrow reconciliation, and associated diagnostics/recovery lessons.

### Important principles
- A purchase intent is not the same as a completed payment.
- A local â€œpurchase in progressâ€ state must be reconciled against the actual intent and provider evidence.
- A valid active intent or an Escrow-linked/protected transaction must not be deleted or unlocked casually.
- A completed/sold listing must not be unlocked merely because a UI label looks stale.
- A missing intent row can leave an orphaned lock if the lock and intent record are stored separately; Lesson 239 specifically addresses this failure mode.

### Live transaction creation is opt-in
The plugin defines this safety gate:

```php
if ( ! defined( 'FLIPNZEE_ENABLE_LIVE_BUY_NOW' ) ) {
    define( 'FLIPNZEE_ENABLE_LIVE_BUY_NOW', false );
}
```

Live Buy Now transaction creation is disabled by default. Do not enable it by defining the constant as `true` until the live provider workflow, account, environment, webhook/reconciliation behaviour, failure handling, and administrator recovery steps have been reviewed and tested.

If production activation is deliberately approved, define the constant in `wp-config.php` before the plugin loads:

```php
define( 'FLIPNZEE_ENABLE_LIVE_BUY_NOW', true );
```

**Do not copy this production enablement into a site without completing the review.** Keep the default false setting for development and unverified deployments.

---

## Lesson 239: stale-lock recovery and bid unblocking

The package includes `LESSON-239-STALE-BUY-NOW-LOCK-BID-UNBLOCK.md`.

### Problem addressed
A test/spam Buy Now lead could be deleted from `wp_flipnzee_buy_now_intents` while the separate WordPress option `flipnzee_marketplace_sale_lock_{listing_id}` remained. Both the bid handler and frontend consult `Flipnzee_Buy_Now_Intent::is_listing_locked()`. As a result, the page could continue to show **Purchase in progress** and reject bids even though the intent row was gone and the auction was legitimately active.

### Safeguards in the fix
The implementation is intended to:
- Reconcile a shared lock when its referenced intent is missing, terminal, or expired.
- Inspect all intent rows before removal for active/protected purchase states and Escrow transaction references.
- Fail closed if the listing has an auction transaction row; do not clear a sold lock.
- Re-read the lock immediately before deletion to avoid deleting a lock that was replaced by a newer checkout.
- Run the same safe reconciliation after an administrator successfully deletes an unlinked test/spam intent.
- Leave auction status, auction end time, bid history, provider transactions, and valid active purchase intents unchanged.

### Intended result
For an active auction with no valid in-progress or completed purchase, an orphaned stale Buy Now lock can be removed. The â€œPurchase in progressâ€ label disappears and bids can be accepted again.

An active auction alone is not a reason to retain a stale Buy Now lock. Conversely, a sold state or protected provider transaction is not safe to unlock just to make bidding available.

### Validation still required
The lesson notes call for PHP lint and ZIP integrity checks. The live database and frontend must still be verified after installation. Test on staging before relying on the fix in production.

---

## Payments and payment providers

The package includes:
- `includes/class-payment-manager.php`
- `includes/class-transaction-manager.php`
- `includes/class-transaction-state-manager.php`
- `includes/class-transaction-lifecycle-manager.php`
- `includes/class-auction-payment-resolution.php`
- `includes/class-payment-page.php`
- `admin/class-admin-payments.php`
- `admin/class-admin-transactions.php`
- `admin/class-admin-transaction-details.php`

These components separate payment-provider selection, transaction persistence, state transitions, payment resolution, frontend payment display, and administration.

### Provider status
The payment manager contains gateway logic for the providers represented in the installed source, including Manual and Stripe paths, while Escrow has dedicated provider/API classes. Provider availability is configuration- and workflow-dependent. A gateway label in code is not proof that the provider is configured, approved, or ready for production.

### Manual payments
Manual payment handling requires an administrator to verify payment externally and update the relevant transaction/payment state through the supported admin flow. Keep a written record of external payment evidence and ensure status changes are auditable.

### Stripe
Use only if the installed settings and integration are configured for your Stripe account. Test success, failure, cancellation, retries, and duplicate callbacks in the appropriate test environment. Do not mark transactions paid based solely on a browser redirect.

### General payment rules
- Never store card details in WordPress.
- Use provider-hosted checkout where implemented.
- Treat provider-side status as the authority for settlement.
- Reconcile callbacks/webhooks and local transaction records.
- Keep credentials out of source control.
- Test idempotency and duplicate event handling.
- Do not equate payment initiation with payment completion.

---

## Escrow.com integration

The package includes:
- `includes/class-escrow-api-client.php`
- `includes/class-escrow-provider.php`
- `includes/class-escrow-webhook.php`
- `admin/class-admin-escrow-settings.php`
- Buy Now intent and transaction reconciliation components.

The integration is designed to support an escrow-backed transaction workflow for digital assets. Escrow is a third-party service; its account configuration, transaction state, inspection terms, payment status, and release conditions remain subject to Escrow.com and the specific transaction agreement.

### Before connecting
1. Confirm whether you are configuring Sandbox or Production.
2. Use credentials belonging to that same environment.
3. Confirm the API base/environment and account identity.
4. Configure the webhook/callback URL as required by the integration and provider account.
5. Verify that provider events are actually delivered and reconciled.
6. Test creation, cancellation, expiry, inspection, completion, and failure scenarios.
7. Keep production Buy Now creation disabled until the whole path is verified.

### Webhook/reconciliation
A successful local HTTP request is not sufficient evidence that a webhook lifecycle works end to end. Verify:
- The event was emitted by the provider.
- The provider attempted delivery to the expected URL.
- The endpoint received and authenticated/validated the event as implemented.
- The transaction or intent was matched to the correct local record.
- The resulting state was persisted.
- Replayed or duplicate events do not cause duplicate transitions.

### Sandbox vs production
Never infer production readiness from a successful sandbox test. Sandbox and production can differ in credentials, transaction identifiers, provider settings, permissions, and webhook registration. Keep evidence of the environment used for each test.

### Failure handling
If provider state and local state disagree, use the administrator diagnostics/reconciliation tools and verify the provider record. Do not manually delete provider-linked records or clear locks as a shortcut.

---

## Transactions, purchases, and transfers

### Transaction management
The transaction manager and lifecycle/state classes centralize transaction records and status changes. Admin pages support reviewing transactions and their details.

### Buyer purchases
`[flipnzee_my_purchases]` is the buyer's purchase-list page. `[flipnzee_purchase_details]` provides a detailed view. Test that each buyer can access only their own purchase data unless an explicit administrator capability allows otherwise.

### Payment page
`[flipnzee_payment_page]` provides the frontend payment interface. It should be reached through the site's intended transaction flow and must not be treated as a standalone payment confirmation.

### Transfer management
Digital asset transfer often requires manual coordination (for example, domain registrar changes, website files/database migration, or account handover). The plugin has administrator transfer tools to track progress. Do not claim ownership automatically transfers just because an auction ends or a payment begins.

---

## Watchlists

The package includes:
- `includes/class-watchlist-manager.php`
- `includes/class-watchlist-ajax.php`
- `includes/class-watchlist-manager.php` integration in the main loader
- `[flipnzee_watchlist]`

The watchlist feature provides a user-facing way to keep track of listings. Test the logged-in and logged-out experiences and the add/remove actions. Confirm that AJAX nonces and user ownership checks are enforced and that a user cannot read or change another user's watchlist.

---

## Administration

Admin classes in the package include:

| Admin component | Purpose |
|---|---|
| `admin/class-admin.php` | Main administration menu/pages |
| `admin/class-auctions-table.php` | Auction administration table |
| `admin/class-admin-posts.php` | Listing/post-related admin integration |
| `admin/class-admin-payments.php` | Payment administration |
| `admin/class-admin-transactions.php` | Transaction list and management |
| `admin/class-admin-transaction-details.php` | Transaction detail view |
| `admin/class-admin-buy-now-intents.php` | Buy Now intent management and recovery actions |
| `admin/class-admin-escrow-settings.php` | Escrow configuration |
| `admin/class-admin-transfer.php` | Ownership transfer management |
| `admin/class-admin-activity-log.php` | Activity log |

The exact menu labels depend on the active code path and the user's capabilities. Use an administrator account for setup and diagnostics; test frontend pages with a regular buyer account.

---

## Database and persistence

The plugin declares database schema version `1.9.3` and includes database creation/migration classes. Table names use the active WordPress database prefix, so `wp_` in examples is a common default, not guaranteed.

The package's workflows reference these data structures:

| Structure | Purpose |
|---|---|
| Auctions table | Auction configuration and status |
| Bids table | Bid history and bidder identifiers |
| Transactions table | Transaction and payment lifecycle records |
| Buy Now intents table (`{prefix}flipnzee_buy_now_intents`) | Purchase intents and their state/provider references |
| Activity log table | Audit trail for relevant operations |
| WordPress options `flipnzee_marketplace_sale_lock_{listing_id}` | Shared per-listing Buy Now lock state |
| WordPress post records | Listing content referenced by auction cards |
| WordPress user metadata/options | User and plugin settings where applicable |
| WordPress scheduled events | Maintenance/reconciliation scheduling |

Use the plugin's migration/activation code to maintain schema. Do not edit tables directly unless you understand the state machine and have a tested backup/rollback procedure.

### Important state separation
The auction record, bid history, Buy Now intent, shared lock option, payment transaction, Escrow transaction, and ownership-transfer record represent different facts. A repair should reconcile these records, not delete unrelated records to make a frontend label disappear.

---

## Hooks, scheduling, and assets

### Scheduled hooks
The main plugin registers maintenance hooks including:
- `flipnzee_auction_maintenance`
- `flipnzee_escrow_reconcile_maintenance`

The auction maintenance hook is scheduled hourly by the plugin's activation/maintenance flow. The exact work depends on the relevant maintenance callback and provider configuration. WordPress cron is traffic-driven by default and may not run exactly on the hour on low-traffic sites.

### Admin/frontend hooks
The main plugin also uses:
- `plugins_loaded`
- `admin_enqueue_scripts`
- `wp_enqueue_scripts`
- `admin_post_flipnzee_update_payment_status`
- `admin_post_flipnzee_update_transaction_status`
- `admin_post_flipnzee_update_transfer`

Other classes register additional actions and AJAX hooks for bidding, watchlists, Buy Now intents, notifications, and reconciliation. Inspect the installed source when integrating custom code, since hook names and parameters are part of the implementation contract.

### Assets
The plugin contains frontend and admin assets under its `assets/` directory. The main plugin enqueues frontend styles/scripts and admin assets on their relevant screens. If styling or JavaScript appears stale after an upgrade, clear page/CDN caches and verify that the browser is loading the current plugin asset version.

---

## Clicky Analytics and other analytics integrations

### Clicky
**Clicky Analytics is not integrated by this v1.12.65 package.** There is no Clicky API client, Clicky authentication configuration, Clicky API request, or Clicky-specific shortcode in the inspected source.

The auction card has an optional display block that reads existing WordPress transients:
- `flipnzee_main_{listing_id}` â€” may contain values such as `users` and `sessions`.
- `flipnzee_meta_{listing_id}` â€” may contain values such as `organic_impressions`.

The UI labels this block â€œGoogle Verified Analytics.â€ This plugin does not establish which other component populated those transients. Their presence should not be described as proof of Clicky data.

If Clicky support is required, it should be designed explicitly, including:
- Administrator configuration for a Clicky site ID and API token.
- Server-side API requests with token secrecy.
- Cache/refresh controls and API error handling.
- Clear attribution of Clicky metrics vs GA4 and Search Console metrics.
- Privacy, permissions, rate limiting, and deletion behaviour.
- Tests for missing credentials, invalid responses, quotas, and stale cache.
- A documented shortcode or display integration only after it exists in code.

### Google Analytics and Search Console
This package does not itself implement a complete Google OAuth/API client in the inspected files. It can display cached analytics transients if another component has populated them. Do not treat the presence of the display block as proof that this plugin independently fetches GA4 or Search Console data.

---

## Security

Before deploying publicly:
- Keep WordPress, PHP, and dependencies updated.
- Use HTTPS.
- Store provider credentials securely and never commit them to Git.
- Use least-privilege provider credentials and test/sandbox credentials during development.
- Verify capability checks and nonces for every administrative action.
- Ensure users can only access their own purchase and watchlist information.
- Sanitize request input and escape output.
- Verify provider webhook authenticity and handle duplicate/replayed events safely.
- Preserve audit logs for payment and transaction state changes.
- Keep the live Buy Now safety constant disabled until production readiness is proven.
- Do not manually clear a listing lock without checking all related intents, transactions, and auction state.
- Back up database and files before migrations or recovery operations.

---

## Troubleshooting

### â€œNo active auctions foundâ€
- Confirm an auction exists and is active.
- Confirm its associated listing post exists.
- Confirm the auction end time is in the future and timezone handling is correct.
- Check the auction record and plugin logs if it still does not appear.

### Auction card is missing a thumbnail
Add a featured image to the associated listing. The shortcode uses a placeholder when no featured image exists.

### Bids are rejected
- Confirm the auction is still active.
- Check the current bid and bid validation rules.
- Check whether a valid Buy Now intent is holding the listing.
- If the lock is stale, inspect the Buy Now intent/admin diagnostics and use the safe recovery path. Do not delete lock options manually without verifying provider/transaction state.

### â€œPurchase in progressâ€ remains after a test lead was deleted
This may be an orphaned listing lock. Lesson 239 adds safe reconciliation for eligible missing/terminal/expired intent cases, with checks for protected intents, Escrow references, auction transaction rows, and lock replacement. Upgrade to the intended package, inspect the admin diagnostics, and verify the live result. Do not force-unlock a sold or provider-linked transaction.

### Buyer purchase page is empty
- Test while logged in as the buyer.
- Confirm the transaction belongs to that user.
- Verify that the purchase/transaction record was created by the intended workflow.
- Check the relevant admin transaction page and logs.

### Payment status is inconsistent
- Check the provider's own transaction record.
- Verify that the transaction belongs to the correct environment (Sandbox vs Production).
- Review webhook delivery and reconciliation evidence.
- Do not treat a return URL or a user screenshot as definitive settlement evidence.

### Escrow setup does not work
- Verify environment, credentials, seller identity, API permissions, webhook URL, and provider-side delivery.
- Check plugin diagnostics and logs.
- Test a complete sandbox lifecycle, not just transaction creation.
- Do not enable production Buy Now creation until reconciliation and recovery are verified.

### Watchlist does not update
- Confirm the frontend script loads.
- Check browser console and network requests.
- Check AJAX/nonce responses and logged-in state.
- Rule out caching, CDN, security plugin, or JavaScript conflicts.

### Scheduled maintenance does not run
WordPress cron can be delayed on low-traffic sites. Confirm scheduled events exist and that the hosting environment permits WP-Cron or a configured system cron.

---

## Testing and production checklist

### Basic auction tests
- [ ] Plugin activates without PHP errors.
- [ ] Database schema is current.
- [ ] Auction card renders for a valid active auction.
- [ ] Missing listing posts are handled safely.
- [ ] Featured-image and placeholder paths work.
- [ ] Start/current prices and status labels are correct.
- [ ] Bid success and failure messages display correctly.
- [ ] Bidder labels do not expose public usernames.

### Buy Now/lock tests
- [ ] Valid intent prevents conflicting purchase/bid actions.
- [ ] Intent state and lock state agree.
- [ ] Expired/cancelled/terminal intents are reconciled safely.
- [ ] Missing intent does not leave an indefinite stale lock.
- [ ] Protected provider-linked intents are not unlocked.
- [ ] Sold/completed listings are not unlocked.
- [ ] A replacement lock is not deleted by a concurrent recovery.
- [ ] Lesson 239 behaviour is verified against the live database and frontend.

### Payment/Escrow tests
- [ ] Sandbox and production credentials are not mixed.
- [ ] Transaction creation is disabled by default unless explicitly approved.
- [ ] Provider webhook delivery is evidenced.
- [ ] Duplicate/replayed events are handled safely.
- [ ] Failure, cancellation, expiry, and completion states are tested.
- [ ] Admin and buyer email notifications are tested.
- [ ] Transfer state is not confused with payment settlement.

### Buyer/watchlist tests
- [ ] Buyer sees their own purchases.
- [ ] Unauthorized users cannot view another buyer's details.
- [ ] Watchlist add/remove works.
- [ ] User data remains isolated.
- [ ] Logged-out empty states and login links work.

### Production readiness
- [ ] Backup and rollback plan exists.
- [ ] Logs do not expose credentials or sensitive buyer data.
- [ ] API credentials are stored securely.
- [ ] The production workflow has been reviewed by the site owner.
- [ ] Clicky integration is not advertised unless separately implemented and tested.

---

## Version 1.12.65 notes

The supplied package identifies itself as version **1.12.65** and includes Lesson 239, â€œStale Buy Now Lock Must Not Block Active Auction Bidding.â€

The Lesson 239 change addresses an orphaned lock left after a false/test Buy Now lead is removed. It reconciles eligible locks only after checking active/protected purchase intents, Escrow references, auction transaction rows, and the current lock value. The intended result is to unblock bidding for a legitimately active auction without modifying bid history, auction end time/status, provider transactions, or valid purchase intents.

See `LESSON-239-STALE-BUY-NOW-LOCK-BID-UNBLOCK.md` and `CHANGELOG.md` included in the source package for the release notes and supporting implementation history.

---

## Known limitations and integrations not included

- **Clicky Analytics:** No Clicky API integration exists in this package.
- **Independent GA4/Search Console API collection:** The optional auction-card analytics panel reads pre-existing transients; it does not by itself prove this plugin fetches those services.
- **Automatic asset transfer:** Website/domain ownership transfer still requires the configured transfer workflow and often manual action.
- **Production Buy Now:** Disabled by default behind `FLIPNZEE_ENABLE_LIVE_BUY_NOW`.
- **Provider readiness:** Provider classes do not prove that credentials, permissions, webhooks, and production transaction flows are fully configured.
- **Live verification:** PHP lint/ZIP integrity and static source review cannot prove that the production database, external provider, scheduled tasks, emails, and frontend all work correctly. Test on the actual staging/production environment.

---

## Source and support

- **Website:** https://flipnzee.com
- **GitHub organization:** https://github.com/SplendidDigital
- **Repository:** https://github.com/SplendidDigital/flipnzee-auctions

When reporting an issue, include:
- Plugin version (`1.12.65`).
- WordPress and PHP versions.
- The relevant shortcode/page or admin action.
- Expected and actual behaviour.
- Steps to reproduce.
- Relevant non-secret log messages and transaction state timeline.

**Never include API keys, passwords, OAuth secrets, access tokens, or private buyer information in a public GitHub issue.**
