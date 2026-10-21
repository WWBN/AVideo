# StripeYPT configuration

StripeYPT handles card payments for YPTWallet, PayPerView movie rentals, PayPerViewLive events and recurring Subscription plans. Enable YPTWallet and its `enablePlugin_YPTWalletStripe` option. Enable each content plugin you intend to use. Independently distributed plugins need compatible checkout versions; an approved card payment alone does not establish access to a movie or live event.

## Configure in AVideo

1. Select the correct Stripe account and sandbox. Copy its publishable key and create a restricted server key with the permissions below. Do not post keys in issues or commit them.
2. In **Admin Panel → Plugins → StripeYPT → Edit parameters**, save `Publishablekey` and `Restrictedkey`. Defaults are blank. There is no separate sandbox switch: `pk_test_` with `rk_test_` (or `sk_test_`) uses test mode; live keys use real payments. Prefixes must match, and both keys must belong to the same Stripe account.
3. Open **Check configuration → Run checks**. Review every failure and every item marked for further verification.
4. Click **Configure webhook automatically** if needed. With **Webhook Endpoints: Read + Write**, the tool creates the site's endpoint with the two required events and a pinned API version, then saves its new signing secret encrypted on the server. It never returns that secret to the browser. If one exact matching endpoint already exists, it enables it and adds missing events, preserving other events and existing secrets. A correct existing endpoint needs no write operation.
5. If an endpoint already exists, its secret cannot be recovered with the v1 API. Reveal its secret in Stripe and paste it into `SigningSecret`. Automatic setup does not overwrite this field. If creation succeeded but saving failed, use this same manual recovery before accepting payments.
6. Complete the sandbox tests below. API checks do not replace a signed webhook delivery or an actual checkout/playback test.
7. To go live, repeat setup with the live account's keys and webhook secret. Keep test and live credentials separate.

Automatic configuration is an explicit administrator **POST** protected by `globalToken`. It is serialized across PHP workers and uses Stripe idempotency for creation. Ordinary payments and IPNs no longer list or create webhook endpoints. The legacy `createWebhook.json.php` URL remains an alias for the secured checker: POST with a valid token and `action=check` (default) or `action=configure`. Its `webhook` field contains only safe endpoint details.

| Setting | Purpose |
| --- | --- |
| `Publishablekey` | Browser key, `pk_test_…` or `pk_live_…` |
| `Restrictedkey` | Private server key, preferably `rk_test_…` or `rk_live_…`. Full `sk_` keys are supported but grant more access. |
| `SigningSecret` | Manually configured webhook secret, `whsec_…`. Comma-separated secrets are supported during rotation. |
| `automaticWebhookSecret` | Internal encrypted secret and binding saved by automatic creation. Valid only for the same server key and webhook URL; changing either requires configuration review. Keep the installation's encryption configuration when restoring backups. |
| `paymentButtonLabel` | One-time payment button text |
| `subscriptionButtonLabel` | Recurring subscription button text |

## Required webhook

| Property | Required value |
| --- | --- |
| URL | The installation's public HTTPS base URL + `plugin/StripeYPT/ipn.php` (including any installation subdirectory) |
| Account | Your own account, in the same sandbox/live mode as the API keys |
| Payload | **Snapshot**, not thin events |
| API version | **2022-11-15**, matching StripeYPT's API requests |
| Events | `charge.succeeded`, `invoice.payment_succeeded` |
| Status | Enabled |

The tool paginates endpoints and matches the exact URL. It will not choose between duplicates, replace an endpoint with a different API version, delete unrelated endpoints, rotate secrets or change API key permissions. Version changes and duplicate cleanup need review in Stripe. An endpoint with `api_version=null` follows the account default and is not pinned.

Your domain, TLS, reverse proxy and forwarded ports must allow **Stripe's external servers** to POST to this URL. A successful local HTTP request or a well-formed HTTPS URL does not establish external reachability. Stripe CLI forwarding has a different signing secret from a Dashboard endpoint; use the secret of the delivery source you are actually testing.

## Restricted key permissions

Write permission includes read access. Stripe Dashboard labels can vary; check the resource corresponding to each API below.

| Feature | Stripe resource | Required access | Reason |
| --- | --- | --- | --- |
| Wallet, PPV and PPV Live card checkout | PaymentIntents | Write | Create/read card payment intents |
| All payment flows | Charges | Read | Read actual charge timing, refunded amount and invoice reference; verify safe fulfillment |
| Configuration checker | Webhook Endpoints | Read | Inspect endpoint URL, mode, status, events and API version |
| Automatic webhook setup | Webhook Endpoints | Write | Create or enable/update the site's endpoint; unnecessary at payment runtime if configured manually |
| Recurring subscriptions / saved cards | Customers | Write | Create/retrieve customers and update their payment method |
| Recurring subscriptions / saved cards | PaymentMethods | Write | Retrieve saved payment methods and related card operations |
| Recurring subscriptions | Plans / Prices | Write | Create the recurring plan through the Plans API |
| Recurring subscriptions | Products | Write | Plan creation includes creation of its product |
| Recurring subscriptions | Subscriptions | Write | Create, retrieve, update and cancel recurring subscriptions |
| Recurring subscriptions | Invoices | Read | Recover subscription metadata from a charge's invoice when needed |

The checker probes **read access**, using requests with a limit of one, for the resources needed by enabled features. It reports missing access without returning customer/payment records or raw API error messages. It does **not** discover or grant a restricted key's permission list, and a successful GET does **not** prove Write access. Review required Write permissions in Stripe and exercise them in sandbox checkout. Webhook Write is proved only when an actual requested setup change succeeds; an unchanged endpoint does not prove it.

An optional current-account probe reports the account ID and `charges_enabled`. A false value blocks live readiness; in a sandbox it is reported for review, with a request to verify test payments separately. Sandbox success does not prove live activation. If a restricted key cannot read this resource, the report asks for a Dashboard check and continues the payment resource probes. Account read access is optional for this additional diagnostic, not a new checkout requirement. The tool does not submit onboarding information or change the account's capabilities.

Keys can also be limited by IP, account, expiration or Stripe policy. A denied request can require checking those settings as well as the resource permission. The tool uses only StripeYPT's keys; independent integrations with their own Stripe settings (for example DiskUploadQuota) require separate review.

## Payment and access flow

The browser confirms the card, including bank authentication when requested. The server fulfills only after a signed Stripe webhook is processed and the local transaction commits. Card approval, wallet credit and content access are separate observations.

- **Wallet:** `charge.succeeded` credits the wallet once.
- **PPV:** with compatible PayPerView checkout, verified card credit and the selected movie rental are completed together. A failed rental can leave the credit available in the wallet for completing the rental. Check the exact video ID/title and access expiration.
- **PPV Live:** check the exact event schedule. A future event has a purchase confirmation and start time; playback is available when the broadcast starts.
- **Subscription:** `invoice.payment_succeeded` fulfills the recurring payment and renews access once. A related charge event must not renew it again. A first invoice of zero (such as a trial) can still be relevant.

If a customer was charged but sees no rental/subscription, inspect Stripe deliveries and AVideo logs before retrying. Do not charge the customer again while an earlier paid payment is waiting for fulfillment. Repair/retry the existing delivery and confirm the exact purchase and wallet state.

## Sandbox acceptance checks

Use [Stripe's current test cards](https://docs.stripe.com/testing), never real cards in sandbox.

1. Approve a payment with `4242 4242 4242 4242`, a future expiry, any valid CVC and postal code. Confirm the amount, currency and movie/event or plan description in Stripe and the receipt.
2. Confirm a signed delivery to the exact endpoint returned **HTTP 200**. Confirm the matching wallet credit and rental or subscription, then open and play the selected video. For future live events, verify the scheduled confirmation and test playback during a broadcast.
3. Redeliver the same event: it must not credit, rent or renew twice.
4. Test a declined card (`4000 0000 0000 0002`): show an actionable error, with no wallet credit or access.
5. Test 3D Secure with a current Stripe authentication test card (for example `4000 0025 0000 3155`). Complete and cancel authentication. A blank bank frame or an intent still in `requires_action` is **not** a passing result, even if another card works.
6. Check refresh/double-click/pending-webhook behavior, partial wallet balance, an already-owned purchase and the correct post-payment confirmation/redirect. Keep one charge and one entitlement per purchase.

## Troubleshooting

**Card charged, access missing:** check the correct account/mode, exact content metadata, signed delivery and AVideo processing logs. HTTP 200 means the handler acknowledged an event; confirm the resulting purchase too.

**Five-minute duplicate guard:** the guard can intentionally delay a different new one-time payment after a recent successful payment. **Charges: Read** gives the actual Stripe charge timestamp and refund state; granting it does not remove the guard. A paid but uncredited intent can block a new charge for Stripe's retry window (up to three days). Finish or investigate that payment instead of creating another charge.

**Signing secret check says review needed:** presence and format do not prove the secret belongs to the endpoint. After this version receives and successfully handles a real signed payment event, it caches the event type and reception time for up to seven days. The report shows that historical evidence for the current server key, URL and secret set. A settings change or cache cleanup requires a fresh delivery. This evidence does not prove current reachability or content access; confirm the delivery and resulting purchase in Stripe/AVideo. The tool never signs its own probe and presents it as evidence that Stripe delivery works.

**No webhook inspection permission:** configure it manually in Stripe using the table above. The checkout does not require webhook-management access. Add Read only if you want the tool to inspect it, and Write only if you want automatic configuration.

References: [restricted API keys](https://docs.stripe.com/keys/restricted-api-keys), [create webhook endpoint](https://docs.stripe.com/api/webhook_endpoints/create), [update webhook endpoint](https://docs.stripe.com/api/webhook_endpoints/update), [receiving webhooks](https://docs.stripe.com/webhooks).
