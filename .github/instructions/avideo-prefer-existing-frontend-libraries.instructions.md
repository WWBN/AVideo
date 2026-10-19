---
name: "AVideo Prefer Existing Frontend Libraries"
description: "Use when adding or changing frontend UI, JavaScript, CSS, forms, modals, tables, charts, media players, editors, uploaders, date pickers, or visual interactions in AVideo. Requires prompts and implementations to prefer libraries already bundled in the project before adding new dependencies."
applyTo: "{**/*.js,**/*.css,**/*.html,view/**,admin/**,plugin/**/*.php,plugin/**/view/**,plugin/**/page/**}"
---

# AVideo: Prefer Existing Frontend Libraries

## Core Rule

Always prefer frontend libraries, plugins, utilities, and UI patterns that already exist in this repository.

Before suggesting, installing, importing, or coding against a new UI/frontend dependency:

1. Check `package.json` for an existing dependency.
2. Check `view/include/head.php` and `view/include/footer.php` for globally loaded assets.
3. Check `view/js/`, `view/css/`, and `view/bootstrap/` for bundled local libraries.
4. Check similar pages in `view/`, `admin/`, and `plugin/` for the established implementation pattern.
5. Only add a new dependency when no existing library reasonably fits, and explain why.

Do not use external CDN assets. AVideo serves frontend libraries locally through repository files or `node_modules`.

---

## Default UI Stack

AVideo is a legacy jQuery/Bootstrap 3 application. Build new UI in that style unless the user explicitly requests otherwise or the current page clearly uses the Bootstrap 5 compatibility path.

| Need | Prefer |
|---|---|
| Layout and components | Bootstrap 3 already bundled in `view/bootstrap/` |
| DOM and AJAX | jQuery |
| Drag, sort, and basic widgets | jQuery UI / `jquery-ui-dist` |
| Icons | Font Awesome / `@fortawesome/fontawesome-free` |
| Toasts | `jquery-toast-plugin` through `avideoToastSuccess()` / `avideoToastError()` |
| Alerts | SweetAlert or existing AVideo alert helpers |
| Loading state | Existing global `modal.showPleaseWait()` / `modal.hidePleaseWait()` |
| Tables | Bootgrid or DataTables, following the nearest existing page |
| Select dropdowns | Select2 or Bootstrap Select, following nearby code |
| Date/time inputs | Flatpickr or existing `bootstrap-datetimepicker` usage |
| Rich text | TinyMCE |
| Image crop/upload UI | Croppie and bootstrap-fileinput |
| Charts | Chart.js |
| Calendars | FullCalendar |
| Guided tours | Intro.js |
| Lightbox/gallery | GLightbox or Flickity, depending on the existing page pattern |
| Infinite lists | `infinite-scroll` |
| Video playback | Video.js and existing Video.js plugins |
| HLS playback | HLS.js or existing Video.js HLS integration |
| Masks | `jquery-mask-plugin` or `inputmask` |
| Lazy loading | `jquery-lazy` |

---

## Known Bundled Libraries

The project already includes these frontend-related dependencies and local assets. Prefer them before proposing alternatives:

- Bootstrap 3 in `view/bootstrap/`, plus Bootstrap 5 in `node_modules/bootstrap` for optional compatibility
- jQuery and jQuery UI / `jquery-ui-dist`
- Font Awesome: `@fortawesome/fontawesome-free`, `font-awesome`, `fontawesome-free`
- animate.css and `view/css/font-awesome-animation.min.css`
- jquery-toast-plugin, SweetAlert
- Bootgrid, DataTables
- Select2, Bootstrap Select, bootstrap-toggle, bootstrap-list-filter
- bootstrap-fileinput, bootstrap-datetimepicker
- Croppie
- TinyMCE and `tinymce-langs`
- CodeMirror
- Chart.js, chartjs adapters, chartjs-plugin-zoom
- FullCalendar packages
- Flatpickr
- Flickity and `flickity-bg-lazyload`
- GLightbox
- Infinite Scroll
- Intro.js
- Inputmask and jquery-mask-plugin
- js-cookie
- Moment and moment-timezone
- Video.js plus Video.js plugins: ads, IMA, YouTube, playlist, overlay, hotkeys, VR, Chromecast, AirPlay, quality selector, seek buttons, landscape fullscreen
- HLS.js
- NoSleep.js
- Socket.io client
- Dexie, PouchDB
- Workbox service worker files
- Three.js local file in `view/js/three.js`

---

## Existing AVideo Integrations

Some third-party frontend libraries already have AVideo-specific helper functions. Prefer these helpers over direct library initialization because they load local assets, apply AVideo conventions, handle translations, integrate with existing modals/toasts, and avoid duplicate includes.

| Feature | Use This AVideo Integration | Defined In | Notes |
|---|---|---|---|
| Croppie image cropper UI | `getCroppie(...)` | `objects/functionsImages.php` | Returns an array with `html`, `id`, `uploadCropObject`, `getCroppieFunction`, `createCroppie`, and `restartCroppie`. It loads Croppie assets once and includes upload/library/delete controls. |
| Save Croppie result | `saveCroppieImage($destination, $postIndex = "imgBase64")` | `objects/functionsImages.php` | Saves the posted base64 crop result using the same server-side image conversion path as existing AVideo uploads. |
| Croppie element internals | `getCroppieElement(...)` | `objects/functionCroppie.php` | Lower-level helper used by `getCroppie()`. Do not call the raw `.croppie()` plugin unless the helper cannot fit the page. |
| TinyMCE editor | `getTinyMCE($id, $simpleMode = false, $allowAttributes = false, $allowCSS = false, $allowAllTags = false, $allowImageAndMedia = false)` | `objects/functions.php` | Loads TinyMCE once, applies AVideo language files, toolbar presets, GPL license key, valid element rules, and article image upload integration. |
| Intro.js guided tour button | `getTourHelpButton($stepsFileRelativePath, $class = 'btn btn-default', $showLabel = true)` | `objects/functions.php` | Generates the help button that calls `startTour(...)`. The steps file should be JSON with `element` and `intro` entries. |
| Intro.js runtime | `startTour(stepsFileRelativePath)` | `view/js/script.js` | Dynamically loads local Intro.js CSS/JS from `node_modules`, applies the dark theme when needed, fetches tour steps by AJAX, filters hidden elements, then starts the tour. |
| Flickity horizontal navigation | `generateHorizontalFlickity($items)` | `objects/functions.php` | Generates a horizontal button carousel, loads Flickity assets once, supports active item selection, tooltip/tab behavior, and existing Bootstrap button classes. |
| Toast notifications | `avideoToast()`, `avideoToastInfo()`, `avideoToastError()`, `avideoToastSuccess()`, `avideoToastWarning()` | `view/js/script.js` | Wraps `jquery-toast-plugin` with AVideo defaults and reading-time display duration. |
| SweetAlert alerts/confirmations | `avideoAlert*()`, `avideoConfirm()`, `avideoConfirmHTML()` | `view/js/script.js` | Wraps SweetAlert with AVideo button classes, cookie-based once-per-day variants, HTML/text-safe variants, and translated confirm text. |
| iframe modals/dialogs | `avideoModalIframe*()`, `avideoDialog*()` | `view/js/script.js` | Wraps SweetAlert iframe modals, fullscreen behavior, history updates, iframe flags, close handling, and parent-window integration. |
| Loading overlay | `modal.showPleaseWait()`, `modal.hidePleaseWait()`, or a local `getPleaseWait()` instance | `view/js/script.js` | Use these for async work instead of custom spinners. |
| AJAX JSON response handling | `avideoResponse(response)` | `view/js/script.js` | Applies standard success, warning, info, error, toast, alert, and optional `response.eval` behavior. |
| Tooltips | `avideoTooltip(selector, text)` | `view/js/script.js` | Applies the existing Bootstrap tooltip attributes and HTML tooltip option. |

### Croppie Pattern

```php
$croppie = getCroppie(__('Upload Poster'), 'savePosterImage', 1280, 720, 600);
echo $croppie['html'];
?>
<button class="btn btn-success" onclick="<?php echo $croppie['getCroppieFunction']; ?>">
    <i class="fas fa-save"></i> <?php echo __('Save'); ?>
</button>
<script>
function savePosterImage(imageBase64) {
    modal.showPleaseWait();
    $.post(webSiteRootURL + 'objects/mySavePoster.json.php', { image: imageBase64 }, function (response) {
        avideoResponse(response);
    }).always(function () {
        modal.hidePleaseWait();
    });
}
</script>
```

On the server side, receive the same post key with `saveCroppieImage($path, 'image')`.

### TinyMCE Pattern

```php
<textarea id="description" name="description"></textarea>
<?php echo getTinyMCE('description', true); ?>
```

When saving, read content from `tinymce.get('description').getContent()` if the page follows the existing AJAX save pattern.

### Intro.js Tour Pattern

```php
echo getTourHelpButton('plugin/MyPlugin/tour.json');
```

Use a JSON steps file:

```json
[
  { "element": "#firstField", "intro": "Explain this field." },
  { "element": "#saveButton", "intro": "Save changes here." }
]
```

Do not manually add Intro.js `<script>` or `<link>` tags; `startTour()` handles that.

Create an Intro.js tour whenever a UI workflow is moderately complex, especially when it has:

- Multiple required steps or tabs
- Technical setup values, such as live/RTMP/encoder settings
- Bulk actions, status changes, deletion, scheduling, or permission/user-group controls
- Dense reports, charts, filters, or calendar interactions
- A modal with several fields where the next action is not obvious

Follow the existing placement pattern:

- Put the button in the panel heading, page toolbar, modal footer, or action row for the workflow it explains.
- Use `pull-right` when the button sits beside a panel/page title.
- Use `btn btn-default btn-xs` for compact panel headings.
- Use `btn btn-default` with `showLabel = false` when the button is part of a tight icon button group.
- Use `btn btn-default btn-block` when the button is inside a Bootstrap grid column beside a primary action button.
- Keep the button close to the controls it explains, not in a distant global navigation area.

Name the steps file close to the view when possible, using existing conventions such as:

- `view/somePage.help.json`
- `plugin/PluginName/help.json`
- `plugin/PluginName/path/to/view/help.json`
- `plugin/PluginName/feature.help.json`

Each step should point to stable selectors already present in the markup. Prefer IDs for important controls. Add an ID to the target control when needed instead of relying on fragile positional selectors.

### Flickity Pattern

```php
generateHorizontalFlickity([
    ['label' => __('All'), 'href' => '#all', 'tooltip' => __('All items'), 'isActive' => true],
    ['label' => __('Mine'), 'href' => '#mine', 'tooltip' => __('My items')]
]);
```

Use this helper for horizontal tab/button navigation before writing custom Flickity markup.

---

## Dependency Decision Rules

Use this order when implementing frontend features:

1. Reuse an existing AVideo helper or component.
2. Reuse an existing local library already loaded on the page.
3. Reuse an existing bundled library from `node_modules` or `view/`.
4. Add page-specific asset loading with `$_page->setExtraScripts()` / `$_page->setExtraStyles()` or the nearest existing pattern.
5. Add a new dependency only with explicit approval or a clear written justification.

If two existing libraries overlap, follow the nearest existing implementation in the same area of the application. For example, if a plugin's admin pages already use DataTables, continue using DataTables there instead of switching that page to Bootgrid.

---

## Bootstrap Compatibility

The default bundled Bootstrap files live in `view/bootstrap/` and are used by the legacy UI. Bootstrap 5 also exists in `node_modules/bootstrap` and may be enabled conditionally.

When editing existing views:

- Match the Bootstrap version and attributes already used in that file or nearby files.
- Do not mix Bootstrap 3 `data-toggle`/`data-dismiss` patterns with Bootstrap 5 `data-bs-toggle`/`data-bs-dismiss` in the same UI unless the page already has a compatibility layer.
- Prefer existing classes and markup conventions over rewriting the UI for a newer Bootstrap version.

---

## Avoid New Frameworks

Do not introduce React, Vue, Angular, Svelte, Tailwind, Alpine, HTMX, Material UI, Chakra, Bootstrap alternatives, icon packs, charting libraries, table libraries, date picker libraries, or modal/toast libraries unless explicitly requested or justified by a missing capability.

If a prompt asks for a feature that is commonly solved by a new library, first map it to the existing stack:

- "modern modal" -> Bootstrap modal or existing AVideo modal helpers
- "toast notification" -> `avideoToastSuccess()` / `avideoToastError()`
- "confirm dialog" -> SweetAlert / existing alert helpers
- "data grid" -> Bootgrid or DataTables
- "datepicker" -> Flatpickr or bootstrap-datetimepicker
- "WYSIWYG editor" -> TinyMCE
- "crop image" -> Croppie
- "chart/dashboard" -> Chart.js
- "video feature" -> Video.js plugin ecosystem already installed

---

## Do

- Search before choosing a frontend library.
- Prefer bundled local assets over new packages.
- Use existing AVideo JS helpers for modals, loading, toasts, alerts, URLs, and player access.
- Follow nearby UI patterns in `view/`, `admin/`, or the plugin being changed.
- Load page-specific assets using the existing page/plugin asset-loading pattern.
- Mention the existing library chosen when explaining an implementation.

## Do Not

- Add a new npm package just because it is popular.
- Use CDN scripts or styles.
- Introduce a new frontend framework for isolated UI work.
- Reimplement components already covered by Bootstrap, jQuery UI, Bootgrid, DataTables, TinyMCE, Croppie, Chart.js, Flatpickr, or Video.js.
- Mix incompatible Bootstrap markup conventions without checking the page's loaded Bootstrap version.
