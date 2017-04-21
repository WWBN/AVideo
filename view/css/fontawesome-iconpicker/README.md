fontawesome-iconpicker
========================

Font Awesome Icon Picker is a fully customizable plugin for Twitter Bootstrap,
with a powerful base API, based on [bootstrap-popover-picker](https://itsjavi.com/bootstrap-popover-picker/)

You can use Font Awesome or another font icon set of your choice (icon list is totally customizable).

[View demos](https://itsjavi.com/fontawesome-iconpicker/)

## Instantiation

You can call the plugin in several ways:

```javascript
// Create instance if not exists (returns a jQuery object)
$('.my').iconpicker();
$('.my').iconpicker({ /*options*/ }); // you can also specify options via data-* attributes

// For the first matched element, access to a plugin property value
$('.my').data('iconpicker').iconpickerProperty;

// For the first matched element, call a plugin instance method with the given args
$('.my').data('iconpicker').iconpickerMethod('methodArg1', 'methodArg2' /* , other args */);

// Call and apply a plugin method to EACH matched element.
$.iconpicker.batch('.my', 'iconpickerMethod', 'methodArg1', 'methodArg2' /* , other args */); ->
```

## Triggered Events

All of them exposes the plugin instance through event.iconpickerInstance

In order of call:

* iconpickerCreate
* iconpickerCreated
* iconpickerShow
* iconpickerShown
* iconpickerSelect (also exposes event.iconpickerItem and event.iconpickerValue)
* iconpickerUpdate
* iconpickerInvalid (also exposes event.iconpickerValue)
* iconpickerSetValue (also exposes event.iconpickerValue)
* iconpickerSetSourceValue (also exposes event.iconpickerValue)
* iconpickerUpdated
* iconpickerSelected (also exposes event.iconpickerItem and event.iconpickerValue)
* iconpickerHide
* iconpickerHidden
* iconpickerDestroy
* iconpickerDestroyed

## Popover placement extensions

This plugin comes with more placement options than the original Bootstrap Popover.
Here are all the possibilities in detail:

            1 2 3 4 5
            G       6
            F       7
            E       8
            D C B A 9
            
    0.      inline (no placement, display as inline-block)
    1.      topLeftCorner
    2.      topLeft
    3.      top (center)
    4.      topRight
    5.      topRightCorner
    6.      rightTop
    7.      right (center)
    8.      rightBottom
    9.      bottomRightCorner
    A.      bottomRight
    B.      bottom (center)
    C.      bottomLeft
    D.      bottomLeftCorner
    E.      leftBottom
    F.      left (center)
    G.      leftTop


## Available options

```javascript
var defaults = {
    title: false, // Popover title (optional) only if specified in the template
    selected: false, // use this value as the current item and ignore the original
    defaultValue: false, // use this value as the current item if input or element value is empty
    placement: 'bottom', // (has some issues with auto and CSS). auto, top, bottom, left, right
    collision: 'none', // If true, the popover will be repositioned to another position when collapses with the window borders
    animation: true, // fade in/out on show/hide ?
    //hide iconpicker automatically when a value is picked. it is ignored if mustAccept is not false and the accept button is visible
    hideOnSelect: false,
    showFooter: false,
    searchInFooter: false, // If true, the search will be added to the footer instead of the title
    mustAccept: false, // only applicable when there's an iconpicker-btn-accept button in the popover footer
    selectedCustomClass: 'bg-primary', // Appends this class when to the selected item
    icons: [], // list of icon classes (declared at the bottom of this script for maintainability)
    fullClassFormatter: function(val) {
        return 'fa ' + val;
    },
    input: 'input,.iconpicker-input', // children input selector
    inputSearch: false, // use the input as a search box too?
    container: false, //  Appends the popover to a specific element. If not set, the selected element or element parent is used
    component: '.input-group-addon,.iconpicker-component', // children component jQuery selector or object, relative to the container element
    // Plugin templates:
    templates: {
        popover: '<div class="iconpicker-popover popover"><div class="arrow"></div>' +
            '<div class="popover-title"></div><div class="popover-content"></div></div>',
        footer: '<div class="popover-footer"></div>',
        buttons: '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">Cancel</button>' +
            ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">Accept</button>',
        search: '<input type="search" class="form-control iconpicker-search" placeholder="Type to filter" />',
        iconpicker: '<div class="iconpicker"><div class="iconpicker-items"></div></div>',
        iconpickerItem: '<a role="button" href="#" class="iconpicker-item"><i></i></a>',
    }
};
```

## TO-DO
- [x] Support other icon fonts
- [x] Fix: input marked as error when it is empty
- [x] Add role=button to each item, for making them tabbable
- [x] Ability to use the user input as the filter instead of the popover input
- [x] Fix bottomRight position when using component mode
- [x] Better dropdown compatibility
- [x] Ability to mix various class prefixes and use a customizable class formatter function
- [ ] ~~Ability to select and copy the icon character (the would need to be hardcoded in js)~~ (Use the chrome extension)