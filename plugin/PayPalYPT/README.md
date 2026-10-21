# PayPalYPT configuration

PayPalYPT lets buyers pay with PayPal for wallet top-ups, PayPerView movie rentals, PayPerViewLive events and recurring Subscription plans. It needs YPTWallet with `enablePlugin_YPTWalletPayPal` on. FansSubscriptions also uses it for recurring payments. PayPal Payouts (automatic creator withdrawals) is optional.

## APIs used

| Flow | PayPal API (installed SDK: `paypal/rest-api-sdk-php` 1.6.4, plus `paypal/paypal-payouts-sdk`) | Status at PayPal |
| --- | --- | --- |
| Wallet, PPV, PPV Live (one-time) | Payments v1 `/v1/payments/payment` (create, get, execute; `intent=sale`) | Legacy/deprecated, still served |
| Subscription and FansSubscriptions (recurring) | Billing Plans/Agreements v1 `/v1/payments/billing-plans`, `/v1/payments/billing-agreements` | Deprecated; kept for existing plans and agreements |
| Notifications | Webhooks v1 `/v1/notifications/*` and IPN (`ipn.php`, `ipnV2.php`) | Webhooks current; IPN legacy |
| Withdrawals | Payouts v1 `/v1/payments/payouts` | Current |

The files called "V2" (`ipnV2.php`, `requestSubscriptionV2.json.php`) use Billing Agreements v1 too. They are not PayPal Orders v2 or Subscriptions v1.

## Configure in AVideo

1. In [developer.paypal.com](https://developer.paypal.com/dashboard/applications/sandbox) create **your own** REST app, first under **Sandbox**. The `ClientID`/`ClientSecret` shipped with the plugin belong to a **shared public sandbox app** used by many sites. Its webhooks and notifications are not yours. Replace both values.
2. In **Admin Panel → Plugins → PayPalYPT → Edit parameters**, save `ClientID` and `ClientSecret`. Leave `disableSandbox` (**Live payments**) unchecked for a Sandbox app. Check it only with a Live app's credentials. The environment comes from this option, never from the Client ID format.
3. Run the PayPalYPT plugin update if the Plugins page asks for it. Version 3.0 creates the `PayPalYPT_checkout` table. Checkout refuses new payments until it exists.
4. Open **PayPalYPT → Check configuration → Run checks**. Fix every item under **Action required**. Review every item under **Not proven / review**.
5. Click **Configure webhook automatically** (see below), or create the webhook manually and paste its ID into `webhookId`.
6. Run the sandbox tests at the end of this page. API checks do not prove a purchase, a delivery or playback.
7. To go live, create the Live app, save its credentials, check `disableSandbox`, run the checks again and configure the Live webhook. Sandbox and Live apps, buyers and webhooks are separate.

| Setting | Purpose |
| --- | --- |
| `ClientID`, `ClientSecret` | Credentials of your REST app (same environment as `disableSandbox`) |
| `disableSandbox` | Off = Sandbox (`api-m.sandbox.paypal.com`, `ipnpb.sandbox.paypal.com`). On = Live (`api-m.paypal.com`, `ipnpb.paypal.com`) |
| `webhookId` | ID of this site's webhook. Webhook signatures are verified with it. Filled by automatic setup. |
| `paymentButtonLabel`, `subscriptionButtonLabel` | Button texts |

## Check configuration (administrator tool)

`plugin/PayPalYPT/configuration.php`, under the PayPalYPT row's menu. Every action is an administrator-only POST with `globalToken`, answered with `Cache-Control: no-store`. Inspection and setup are separate actions. When `$global['disableAdvancedConfigurations']` is set, only the read-only check is allowed.

The check only authenticates (OAuth `client_credentials`) and reads. It creates no payment, plan, agreement, payout or refund. It never deletes webhooks and never changes the app's permissions. It never returns or logs the secret, the access token, `Authorization` headers or payer data. PayPal's error messages are not echoed back.

| Check | What it proves | What it does not prove |
| --- | --- | --- |
| Environment | Which API, approval site and IPN verification URL the settings select | That the buyer and seller accounts belong to the same environment |
| Credentials | Own app vs. the shared demo app | — |
| OAuth authentication | PayPal accepts the ID/secret in this environment; app ID and token lifetime | Product approval for the account |
| Scopes | The scopes PayPal returns for the token, compared with the APIs each enabled feature calls | That a write succeeds; that the account is approved for the product |
| Read probes | `GET` access to Payments v1 and (with Subscription) Billing Plans v1 | Write access. A 404 is reported as inconclusive. 401 = credentials, 403 = permission/capability, 429/5xx = retry later |
| Webhook | A webhook with this exact URL (including any subdirectory) exists in the app, has the handled events, its ID matches `webhookId`, and how many other webhooks the app has (count only; other URLs are not shown) | That PayPal can reach the URL |
| Public HTTPS URL | The webhook/IPN URL is HTTPS on a public host | DNS, TLS, firewall, proxy and ports as seen by PayPal |
| Signed webhook delivery | The last webhook event that was signature-verified and handled, with its time (history, 30 days, cleared with the cache) | Current reachability or a completed purchase. Simulator events are not real purchases. |
| IPN | The last VERIFIED and handled IPN, the IPN and verification URLs | The account's IPN setting, which the API cannot read |
| Wallet currency | The currency is a PayPal REST currency (HUF, JPY and TWD need whole numbers) | That the seller account accepts it |
| End-to-end purchase | Always "Not proven". Requires a sandbox purchase. | — |

Payouts has no read-only probe. Prove it with a sandbox payout; never with real money.

### Required scopes

| Feature | API | Scope returned by OAuth | Need |
| --- | --- | --- | --- |
| One-time payments | Payments v1 | `https://api.paypal.com/v1/payments/.*` | Required |
| Recurring subscriptions | Billing Plans/Agreements v1 | `https://uri.paypal.com/services/billing-agreements` | Required when Subscription is enabled |
| Webhook check and setup | Webhooks v1 | `https://uri.paypal.com/services/applications/webhooks` | Optional: payments work with a manually configured webhook ID |
| Payouts | Payouts v1 | `https://uri.paypal.com/payments/payouts` | Required only with `enableAutoWithdrawFundsPagePaypal` |

### Automatic webhook setup

**Configure webhook automatically** creates or completes **only this site's webhook**:

- No webhook with this URL: it creates one with the events below, then saves its ID in `webhookId`.
- One webhook with this URL: it adds the missing events and **keeps the other events**. If needed, it saves its ID.
- Several webhooks with this URL, or the app already has 10 (PayPal's limit): it changes nothing. Clean up in developer.paypal.com yourself. **AVideo never deletes webhooks.**
- Shared demo app: refused.

Setup holds a per-site lock, so two administrators or two PHP workers cannot run it at once. A lost create response is reconciled by listing the webhooks again; no second webhook is created. The ID is saved only if the settings did not change during the PayPal call (compare-and-save plus a fresh read). Otherwise the result says so and shows the ID to paste manually.

| Event | Handler |
| --- | --- |
| `PAYMENT.SALE.COMPLETED` | Completes a one-time checkout (re-read from PayPal), or credits and renews a billing-agreement payment |
| `PAYMENT.SALE.DENIED` | Updates the one-time checkout status |
| `PAYMENT.SALE.REFUNDED`, `PAYMENT.SALE.REVERSED` | Marks the checkout and logs a warning for manual review; never adds or removes credit or access |

Older versions registered `PAYMENT.CAPTURE.COMPLETED`, `CHECKOUT.ORDER.COMPLETED` and `CUSTOMER.PAYOUT.COMPLETED`. No handler uses them. Automatic setup keeps them if present, but does not add them.

### Webhook verification

`webhook.php` sends PayPal's signature headers, the **original request body** (byte for byte) and the saved `webhookId` to `POST /v1/notifications/verify-webhook-signature`. Only `SUCCESS` is processed. A delivery is never used to create or change webhooks. AVideo never downloads the certificate URL; it only rejects one that is not on `paypal.com`. Without a saved `webhookId`, a read-only lookup of this URL's webhook is used. PayPal does not use a `whsec_` signing secret.

Before 3.0, signature verification called `PayPal\Api\VerifyWebhookSignature`. That class does not exist in SDK 1.6.4, so every webhook delivery failed with HTTP 500. The old code also created webhooks while validating, and deleted **every webhook of the app** when the limit was reached. That deleted other sites' webhooks on the shared demo app.

HTTP answers: `200` handled or not for this site; `400` not authentic; `500` could not be handled now. PayPal retries non-2xx deliveries up to 25 times over 3 days.

## Payment flow

1. The buyer clicks the PayPal button. The server prices the purchase from the catalog and the wallet: a PPV rental or PPV Live event costs its plan price minus the wallet balance, and a top-up costs the chosen amount. The browser sends only the selection (movie/plan/event IDs) and `globalToken`. The server saves a **checkout** (`PayPalYPT_checkout`: buyer, purpose, exact selection, amount, currency, environment, app) before calling PayPal. The PayPal payment carries the checkout's invoice number and a receipt description with the product name.
2. The buyer approves on PayPal. A double click, refresh or second tab reuses the same PayPal payment. A purchase that is already approved or pending is never charged again.
3. The checkout is completed by whichever comes first: the browser return (`redirect_url.php`), the webhook, the IPN, or the buyer's status page (`plugin/PayPalYPT/checkout.php`). Each reads the payment from PayPal with this site's credentials. It executes it if it is approved and not yet executed (same `PayPal-Request-Id` on retries). It credits only a **completed** sale whose amount, currency and invoice match the checkout. The buyer's session is not needed, and closing the tab after approving does not lose the payment.
4. `PayPalPaymentProcessor` credits the wallet inside `YPTWallet::processPaymentOnce()`, keyed by the PayPal **sale ID**. In the same transaction it rents the selected movie or buys the selected event (`runPaymentStep()` savepoint) and marks the checkout completed. If the rental/event fails (changed price, balance spent elsewhere, disabled plugin), only that step is undone. The money stays in the wallet, and the status page offers to finish the same purchase with the balance.
5. The status page shows each state: waiting for approval, canceled (not charged), confirming, pending at PayPal, completed (with access end and a **Watch Now** / event / wallet button), declined, expired, refunded. It polls for a limited time, then offers **Check payment status**. It clears the session's cached "no access".

Pending sales (`reason_code`, e.g. eCheck) are never credited until PayPal completes them. Denied/failed sales are never credited. A sale refunded or reversed **before** AVideo credited it is not credited. One refunded **after** the credit is flagged for manual review. AVideo does not take back a credit that may already be spent.

### Subscriptions (Billing Agreements v1)

The return URL executes the agreement and links it to the buyer and plan. **An active agreement alone grants nothing.** The first payment (setup fee) is credited and renews the plan once, whichever reports it first: the return (agreement transaction search), `PAYMENT.SALE.COMPLETED` with `billing_agreement_id`, or the IPN (`recurring_payment_profile_created` with `initial_payment_status=Completed`, or `recurring_payment`). All three use the sale ID, so they give one credit and one renewal. Every later cycle is a new sale ID and renews again. With a plan trial, the trial starts without any payment or credit.

A returning subscriber's new agreement is found through its checkout even when the Subscription row still holds the old agreement. Canceling stops future billing at PayPal. It does not shorten a period that was already paid.

Before 3.0, the return credited the plan price before PayPal had charged the setup fee. With `InitialFailAmountAction=CONTINUE` an agreement can be active after a failed setup fee. It then renewed, and a webhook for the same sale could credit and renew again.

### IPN

Verified with `cmd=_notify-validate` against `ipnpb.sandbox.paypal.com` or `ipnpb.paypal.com`, according to `disableSandbox`. Provider IDs are read from the verified raw message, not from `$_POST` after the generic `*_id` integer filter. For one-time checkouts the IPN only triggers a fresh read of the payment, so the credited amount never comes from the notification. If verification cannot complete (network error, unexpected answer), the IPN gets HTTP 503 and PayPal resends it for up to 4 days. A processing failure gets 500. `INVALID` is acknowledged and never processed. Recurring IPNs must belong to an agreement known to this site. Check your account's IPN address (**Account settings → Notifications → Instant payment notifications**) manually; the API cannot read it.

## Logs

SDK logging is disabled: even INFO includes approval tokens in request URLs, and ERROR can include response bodies. The new checkout paths log safe invoice/event/sale/agreement IDs and sanitized API error codes instead. Existing `videos/PayPal.log` files are not removed by this update.

## Sandbox acceptance checks

Use a **Business** seller and a **Personal** buyer from **Sandbox → Accounts**, never real accounts or cards. If you test card funding through PayPal, use only cards PayPal documents for its sandbox.

1. Wallet: pay 10, confirm one credit of 10 and one checkout `completed`. Cancel on PayPal: not charged, no credit.
2. PPV: buy movie A, check the receipt description and the title/ID on the status page, then play it. Repeat with movie B; nothing from A may appear.
3. With a partial wallet balance, PayPal charges only the rest. With a sufficient balance, no PayPal payment is offered.
4. PPV Live: a running test broadcast, a future event (confirmation + start time) and an already purchased event (no payment).
5. Subscription: approval, first payment (one credit + one renewal), a later renewal, a trial plan, cancellation (paid period kept).
6. Developer Dashboard → Webhooks events: the delivery to this URL returned HTTP 200. Resend it: no second credit, rental or renewal.
7. Close the tab right after approving on PayPal, then open the status page or wait for the webhook: the purchase completes without paying again.

Simulator messages and these unit tests do not prove that sandbox money was processed or that access was granted.

## Tests

`tests/Unit/PayPalCheckoutTest.php` (checkout state machine, start/reuse/refusals, signature verification), `tests/Unit/PayPalConfigurationTest.php` (diagnostics and setup), `tests/Security/PayPalConfigurationEndpointTest.php` (admin/POST/CSRF/lock/compare-and-save), `tests/Security/PaymentWebhookIdempotencyTest.php` (one credit and one benefit across return, webhook and IPN).

References: [REST authentication](https://developer.paypal.com/api/rest/authentication/), [webhooks](https://developer.paypal.com/api/rest/webhooks/), [webhook events](https://developer.paypal.com/api/rest/webhooks/event-names/), [Webhooks API v1](https://developer.paypal.com/api/webhooks/v1), [IPN](https://developer.paypal.com/api/nvp-soap/ipn/IPNIntro/), [sandbox testing](https://developer.paypal.com/sandbox-testing/overview).
