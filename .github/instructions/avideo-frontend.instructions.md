---
name: "AVideo Frontend"
description: "Use when writing or reviewing JavaScript, HTML, or CSS in AVideo. Covers the legacy Bootstrap/jQuery UI style used by AVideo, Video.js integration, AJAX conventions, modal/toast/alert patterns, guided intros, and frontend code reuse."
applyTo: "{**/*.js,**/*.html,view/**,plugin/**/view/**,plugin/**/page/**}"
---

# AVideo Frontend Guidelines

## Core Principle: Reuse Before Writing

Before adding any frontend code, search for existing utilities:
- `view/js/script.js` — global modal, toast, alert, player, session utilities
- `view/js/ajaxLoad.js` — AJAX loading patterns
- `view/js/form2JSON.js` — form serialization
- `view/js/session.js` — session management
- `view/js/navbarLogged.js` — logged-in navbar utilities

Do not introduce new frontend frameworks (React, Vue, Angular, etc.) unless explicitly requested.

---

## Framework Stack

| Library | Version | Purpose |
|---|---|---|
| Bootstrap | 3.4.1 default in `view/bootstrap/`; Bootstrap 5 exists only as an optional compatibility path | Layout and components |
| jQuery | bundled in `node_modules/jquery` | DOM manipulation, AJAX |
| Video.js | 8.23.7 | Video player |
| HLS.js | 1.6.16 | HLS streaming |
| Socket.io | 4.8.3 | Real-time communication |
| Chart.js | 4.5.1 | Analytics charts |
| TinyMCE | 8.4.0 | Rich text editing |
| Select2 | — | Enhanced select dropdowns |
| Flatpickr | 4.6.13 | Date/time pickers |
| jQuery UI | 1.14.2 | Drag/sort, UI widgets |
| Intro.js | 8.3.2 | Onboarding tutorials |
| Croppie | 2.6.5 | Image cropping |

Many of these are bundled locally but not all are loaded on every page. Check `view/include/head.php`, `view/include/footer.php`, the current `Page` setup, and nearby files before adding includes. Do not `npm install` additional packages without explicit approval.

---

## Page Asset Loading

When a page needs a library that is bundled but not global, use the existing `Page` asset-loading pattern instead of inline CDN tags:

```php
$_page = new Page(['My Page']);
$_page->setExtraStyles([
    'view/css/DataTables/datatables.min.css'
]);
$_page->setExtraScripts([
    'view/css/DataTables/datatables.min.js'
]);
```

For plugin hooks, return local asset tags from `getHeadCode()` / `getFooterCode()` only when the asset must be injected by the plugin globally. Prefer page-specific `setExtraScripts()` / `setExtraStyles()` when the asset is needed only by that page.

---

## AVideo Library Wrappers

When a library already has an AVideo helper, use the helper instead of initializing the raw plugin directly:

| Library/Need | Preferred AVideo Helper | Where |
|---|---|---|
| Croppie crop/upload UI | `getCroppie(...)` | `objects/functionsImages.php` |
| Save Croppie base64 image | `saveCroppieImage($destination, $postIndex = "imgBase64")` | `objects/functionsImages.php` |
| TinyMCE editor | `getTinyMCE($id, $simpleMode = false, $allowAttributes = false, $allowCSS = false, $allowAllTags = false)` | `objects/functions.php` |
| Intro.js tour button | `getTourHelpButton($stepsFileRelativePath, $class = 'btn btn-default', $showLabel = true)` | `objects/functions.php` |
| Intro.js runtime | `startTour(stepsFileRelativePath)` | `view/js/script.js` |
| Flickity horizontal buttons/tabs | `generateHorizontalFlickity($items)` | `objects/functions.php` |
| Toasts | `avideoToast*()` | `view/js/script.js` |
| SweetAlert alerts/confirms | `avideoAlert*()`, `avideoConfirm()` | `view/js/script.js` |
| iframe modals | `avideoModalIframe*()`, `avideoDialog*()` | `view/js/script.js` |

These wrappers load local assets, reuse AVideo translations and theme behavior, prevent duplicate includes, and keep UI behavior consistent. Use raw `.croppie()`, `tinymce.init()`, `introJs()`, or `.flickity()` only when the existing helper cannot support the required behavior.

---

## Guided Intros

Create an Intro.js tour for moderately complex workflows. Use `getTourHelpButton()` plus a JSON steps file instead of manually loading Intro.js.

Add a tour when a page or modal has multiple steps/tabs, technical setup values, bulk actions, status/deletion controls, permissions/user groups, dense reports, filters, charts, calendar scheduling, or any flow where a first-time user could reasonably hesitate.

Keep the help button near the workflow it explains:

- Panel/page heading: wrap it in `pull-right`; use `btn btn-default btn-xs` for compact headings.
- Toolbar/button group: use `btn btn-default` and pass `false` for the label when space is tight.
- Modal footer or action row: use `btn btn-default btn-block` in a grid column beside the primary action.
- Do not place workflow-specific tour buttons in global navigation unless the whole navigation area is the subject.

Use nearby file naming patterns: `view/page.help.json`, `plugin/PluginName/help.json`, or `plugin/PluginName/feature.help.json`. Prefer stable IDs in the JSON steps and add IDs to target controls when needed.

---

## Loading Indicators

Always wrap async operations with the global `modal` object:

```javascript
modal.showPleaseWait();       // show full-screen spinner

// ... async work ...

modal.hidePleaseWait();       // hide spinner
```

Never implement a custom loading spinner — use `modal.showPleaseWait()`.

---

## User Feedback (Toasts & Alerts)

Use the global AVideo feedback functions — do not use `alert()` or custom notification code:

```javascript
// Success notification (green toast, auto-dismiss)
avideoToastSuccess('Video saved successfully.');

// Error notification (red toast, auto-dismiss)
avideoToastError('Failed to save. Please try again.');

// Error alert modal (requires user to dismiss)
avideoAlertError('You do not have permission to perform this action.');
```

---

## AJAX Pattern

Standard pattern using jQuery AJAX with `modal` feedback:

```javascript
modal.showPleaseWait();
$.ajax({
    type: 'POST',
    url: webSiteRootURL + 'objects/myAction.json.php',
    data: {
        action: 'save',
        videos_id: videoId,
        value: myValue
    },
    success: function (response) {
        avideoResponse(response);
    },
    error: function () {
        avideoToastError('Request failed. Please check your connection.');
    },
    complete: function () {
        modal.hidePleaseWait();
    }
});
```

Global variables available in page context (set by `view/js/script.js`):
- `webSiteRootURL` — site URL with trailing slash
- `_serverTime` — server Unix timestamp
- `timezone` — user timezone string
- `player` — Video.js player instance
- `mediaId` — current media ID
- `isDebuging` — debug mode flag
- `userLang` — browser language

---

## Bootstrap 3 Conventions

AVideo's default UI is Bootstrap 3.4.1 from `view/bootstrap/`. Match the file you are editing.

- Prefer existing Bootstrap 3 patterns: `panel panel-default`, `panel-heading`, `panel-body`, `pull-right`, `btn-xs`, `hidden-xs`, `col-md-*`, `col-sm-*`, `col-xs-*`, `input-group-addon`.
- Use Bootstrap 3 JavaScript attributes in legacy pages: `data-toggle`, `data-target`, `data-dismiss`.
- Use `form-control`, `checkbox`, `radio`, `btn btn-primary`, `btn btn-default`, `btn btn-danger`, and existing `material-switch` patterns.
- Do not introduce Bootstrap 5-only markup such as `btn-close`, `data-bs-*`, `form-select`, `d-none`, `d-flex`, or `col-12` unless the page explicitly loads the Bootstrap 5 compatibility path.
- Bootstrap 5 exists in `node_modules/bootstrap` behind `$global['userBootstrapLatest']`; treat it as optional compatibility, not the default.
- Dark theme support: respect existing theme CSS and variables; do not hardcode colors.

---

## Cross-Theme Compatibility (`view/css/custom/`)

AVideo ships ~20 swappable full Bootswatch 3.3.7 themes in `view/css/custom/` (`default.css`, `cerulean.css`, `cosmo.css`, `cyborg.css`, `darkly.css`, `flatly.css`, `journal.css`, `lumen.css`, `netflix.css`, `paper.css`, `readable.css`, `simplex.css`, `slate.css`, `solar.css`, `spacelab.css`, `standstone.css`, `superhero.css`, `united.css`, `yeti.css`, plus custom user themes), selected per-site/per-user (see `admin/design_colors.php`, `view/css/custom/theme.php`). Each is a complete Bootstrap 3 rebuild with its own color palette, including several dark themes (`cyborg`, `darkly`, `slate`, `solar`, `superhero`).

When adding or changing CSS/markup:

- Never hardcode a color, background, or border that only looks correct on the `default` theme (e.g. a light-gray panel background, black text, white card). Use existing Bootstrap classes (`panel`, `panel-default`, `bg-*`, `text-*`, `well`) or CSS variables so the active theme's palette applies automatically.
- If a custom color is unavoidable, define it once as a CSS custom property with a sensible fallback and let per-theme overrides (if any already exist in `view/css/custom/`) adjust it — do not inline a fixed hex value in a view/plugin file.
- Mentally (or actually) check new UI against at least one light theme and one dark theme (e.g. `default` and `cyborg`/`darkly`) before considering the change done — text-on-background contrast is the most common regression.
- Do not duplicate a theme's CSS rules into a plugin/view stylesheet to "fix" one theme; that breaks the other 19. Fix contrast/spacing with theme-agnostic classes instead.

## Responsive Layout Requirements

Every layout/CSS change must work across Bootstrap 3 breakpoints (`xs`/`sm`/`md`/`lg`):

- Use the Bootstrap 3 grid (`row`, `col-xs-*`, `col-sm-*`, `col-md-*`, `col-lg-*`) and responsive utilities (`hidden-xs`, `visible-xs-*`, `img-responsive`, `table-responsive`) instead of fixed pixel widths.
- Avoid fixed `width`/`height` in `px` on containers that hold text or a variable number of items; prefer `%`, `max-width`, `flex`, or Bootstrap's grid instead.
- Test/consider mobile (`xs`), tablet (`sm`/`md`), and desktop (`lg`) — AVideo has a large mobile-web and in-app-webview audience.
- Reuse existing responsive patterns already in the file/plugin you're editing before inventing a new breakpoint strategy.

## Homepage/First-Page Mode Compatibility

The site's homepage is one of several interchangeable plugins, each with its own markup for listing videos/categories — a site owner can switch between them at any time:

- `plugin/Gallery/` — classic grid/category homepage
- `plugin/YouPHPFlix2/` — Netflix-style "Flix" homepage (rows/carousels per category)
- `plugin/FirstPageChannelList/` — channel-list style homepage
- `plugin/Layout/` — configurable layout/navbar wrapper used across modes

Any CSS/JS change to shared components (navbar, video card/thumbnail, category row, modals, player skin, search results, footer) must keep working in **all** homepage modes the change can reach, not just the one you tested in. Before finishing:

- Check whether the class/component you're touching is rendered from a shared `view/` file (used by multiple modes) or from a mode-specific file under `plugin/Gallery/view/`, `plugin/YouPHPFlix2/view/`, etc. — grep for the class name to find every consumer.
- If a component is shared, verify (or explicitly note as needing manual verification) that the change looks correct in both Gallery's grid layout and YouPHPFlix2's carousel/row layout — their container widths, spacing, and overflow behavior differ significantly.
- Do not scope a fix with a mode-specific class hack (e.g. `.modeFlix .my-widget { ... }`) unless the visual difference between modes is genuinely intentional; prefer a fix that works identically in both.

---

## Reusable CSS/JS Classes (Required)

Before adding a new class, ID, or inline style, search for an existing equivalent and reuse it:

- Grep the relevant CSS files (`view/css/main.css`, the plugin's own CSS, or the nearest existing view) and `view/js/script.js` for an existing class/selector that already does what you need.
- Never write component styling as inline `style="..."` attributes on new markup — add or reuse a CSS class in the appropriate stylesheet (`view/css/main.css` for global/shared UI, the plugin's own CSS file for plugin-specific UI). Inline styles cannot be overridden per-theme and are the most common source of theme/responsive regressions.
- When a new visual pattern is genuinely needed, design it as a **reusable, generically-named class** (e.g. `.video-card`, `.category-row`, `.avideo-badge`) rather than a one-off ID-scoped or page-scoped rule — so the same class can be reused by other views/plugins/homepage modes later instead of being re-invented.
- Prefer composing existing Bootstrap 3 utility classes (`pull-right`, `text-center`, `margin-top-10`, `hidden-xs`, etc.) over writing new CSS for something Bootstrap already solves.
- Keep selectors low-specificity and theme-agnostic (avoid deep nesting, `!important`, or hardcoded colors) so the class keeps working when reused in a different theme or homepage mode.

---

## jQuery Patterns

```javascript
// Document ready
$(document).ready(function () {
    // initialization
});

// Event delegation (for dynamically added elements)
$(document).on('click', '.my-button', function () {
    var id = $(this).data('id');
    // ...
});

// Serialize form data
var formData = $('#myForm').serialize();

// Show/hide with the existing Bootstrap/jQuery style
$('#myElement').toggleClass('hidden');
```

Use current jQuery APIs. Do not use deprecated jQuery methods such as `.live()`, `.die()`, or `$.parseJSON()`.

---

## Video.js

Use the existing `player` global — do not initialize a second Video.js instance on pages where one already exists:

```javascript
// Access the existing player
if (typeof player !== 'undefined' && player) {
    player.play();
    player.pause();
    player.currentTime(0);
}
```

For plugin-added video features, use Video.js plugin API:
```javascript
videojs.registerPlugin('myPluginFeature', function (options) {
    // this = player instance
});
```

---

## Modals

For in-page modals, use the Bootstrap version already used by that page. In the default AVideo UI, this is Bootstrap 3 markup:

```html
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Title</h4>
      </div>
      <div class="modal-body">
        <!-- content -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

Open/close modals programmatically:
```javascript
$('#myModal').modal('show');
$('#myModal').modal('hide');
```

For iframe dialogs, reuse AVideo wrappers such as `avideoModalIframe()`, `avideoModalIframeSmall()`, `avideoModalIframeLarge()`, `avideoModalIframeFullScreen()`, or `avideoDialog()`.

---

## Forms

- Use `form2JSON.js` utilities or jQuery `.serialize()` following nearby code before AJAX submission
- Validate required fields client-side with existing Bootstrap 3 form patterns (`has-error`, `help-block`) when the page already uses them
- Always validate server-side as well — client validation is UX only
- Use `<input type="hidden">` for passing IDs or tokens that should not be visible

---

## Internationalization

AVideo uses server-side locale files in `locale/`. For frontend strings:
- Do not hardcode English-only strings in JavaScript if they are user-facing
- Prefer passing translated strings from PHP into JS via data attributes or inline variables
- Check `locale/` before adding new translation keys

---

## CSS / Styling

- Prefer existing Bootstrap 3 classes and local CSS conventions over custom CSS
- Add custom CSS only in the correct plugin or view CSS file — do not embed `<style>` blocks inline in PHP views
- Use CSS custom properties (`--var`) where theming is needed
- Avoid `!important` unless overriding a third-party library
- Every new class must be responsive (see "Responsive Layout Requirements"), theme-agnostic across all `view/css/custom/` themes including dark themes (see "Cross-Theme Compatibility"), reusable rather than one-off (see "Reusable CSS/JS Classes"), and — if it styles a shared component — verified against every homepage/first-page mode that renders it (see "Homepage/First-Page Mode Compatibility")

---

## Do

- Use `modal.showPleaseWait()` / `modal.hidePleaseWait()` for all async operations
- Use `avideoToastSuccess()`, `avideoToastError()`, `avideoAlertError()` for user messages
- Use the Bootstrap version already used by the page, defaulting to Bootstrap 3 patterns
- Use `webSiteRootURL` for AJAX endpoint URLs
- Use current jQuery patterns (no deprecated methods)
- Reuse existing JS utilities from `view/js/` before writing new ones
- Make every layout/CSS change responsive (Bootstrap 3 grid/breakpoints) and verify it against both light and dark themes in `view/css/custom/`
- Verify shared-component CSS/JS changes across every homepage/first-page mode (Gallery, YouPHPFlix2, FirstPageChannelList, Layout) that renders them
- Reuse an existing CSS class/selector before adding a new one; when a new one is genuinely needed, make it generically named and reusable

## Do Not

- Use `alert()`, `confirm()`, or `prompt()` natively
- Introduce React, Vue, or Angular
- Write custom CSS for things existing Bootstrap/local CSS already handles
- Hardcode absolute URLs — use `webSiteRootURL`
- Use jQuery deprecated methods (`.live()`, `.die()`, `$.parseJSON`)
- Initialize a new Video.js player on a page that already has one
- Import CDN resources — all libraries are bundled locally
- Hardcode colors/spacing that only work correctly on the `default` theme, or on one homepage mode's layout
- Add inline `style="..."` attributes or one-off IDs for new component styling instead of a reusable CSS class
