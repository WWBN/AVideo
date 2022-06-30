## 6.0.0 (2022-06-08)

### Bug fixes

Fix a bug where by-page selection commands sometimes moved one line too far.

## 0.20.0 (2022-04-20)

### Breaking changes

There is no longer a separate `commentKeymap`. Those bindings are now part of `defaultKeymap`.

### Bug fixes

Make `cursorPageUp` and `cursorPageDown` move by window height when the editor is higher than the window.

Make sure the default behavior of Home/End is prevented, since it could produce unexpected results on macOS.

### New features

The exports from @codemirror/comment are now available in this package.

The exports from the @codemirror/history package are now available from this package.

## 0.19.8 (2022-01-26)

### Bug fixes

`deleteCharBackward` now removes extending characters one at a time, rather than deleting the entire glyph at once.

Alt-v is no longer bound in `emacsStyleKeymap` and macOS's `standardKeymap`, because macOS doesn't bind it by default and it conflicts with some keyboard layouts.

## 0.19.7 (2022-01-11)

### Bug fixes

Don't bind Alt-\< and Alt-> on macOS by default, since those interfere with some keyboard layouts. Make cursorPageUp/Down scroll the view to keep the cursor in place

`cursorPageUp` and `cursorPageDown` now scroll the view by the amount that the cursor moved.

## 0.19.6 (2021-12-10)

### Bug fixes

The standard keymap no longer overrides Shift-Delete, in order to allow the native behavior of that key to happen on platforms that support it.

## 0.19.5 (2021-09-21)

### New features

Adds an `insertBlankLine` command which creates an empty line below the selection, and binds it to Mod-Enter in the default keymap.

## 0.19.4 (2021-09-13)

### Bug fixes

Make commands that affect the editor's content check `state.readOnly` and return false when that is true.

## 0.19.3 (2021-09-09)

### Bug fixes

Make by-line cursor motion commands move the cursor to the start/end of the document when they hit the first/last line.

Fix a bug where `deleteCharForward`/`Backward` behaved incorrectly when deleting directly before or after an atomic range.

## 0.19.2 (2021-08-24)

### New features

New commands `cursorSubwordForward`, `cursorSubwordBackward`, `selectSubwordForward`, and `selectSubwordBackward` which implement motion by camel case subword.

## 0.19.1 (2021-08-11)

### Bug fixes

Fix incorrect versions for @lezer dependencies.

## 0.19.0 (2021-08-11)

### Breaking changes

Change default binding for backspace to `deleteCharBackward`, drop `deleteCodePointBackward`/`Forward` from the library.

`defaultTabBinding` was removed.

### Bug fixes

Drop Alt-d, Alt-f, and Alt-b bindings from `emacsStyleKeymap` (and thus from the default macOS bindings).

`deleteCharBackward` and `deleteCharForward` now take atomic ranges into account.

### New features

Attach more granular user event strings to transactions.

The module exports a new binding `indentWithTab` that binds tab and shift-tab to `indentMore` and `indentLess`.

## 0.18.3 (2021-06-11)

### Bug fixes

`moveLineDown` will no longer incorrectly grow the selection.

Line-based commands will no longer include lines where a range selection ends right at the start of the line.

## 0.18.2 (2021-05-06)

### Bug fixes

Use Ctrl-l, not Alt-l, to bind `selectLine` on macOS, to avoid conflicting with special-character-insertion bindings.

Make the macOS Command-ArrowLeft/Right commands behave more like their native versions.

## 0.18.1 (2021-04-08)

### Bug fixes

Also bind Shift-Backspace and Shift-Delete in the default keymap (to do the same thing as the Shift-less binding).

### New features

Adds a `deleteToLineStart` command.

Adds bindings for Cmd-Delete and Cmd-Backspace on macOS.

## 0.18.0 (2021-03-03)

### Breaking changes

Update dependencies to 0.18.

## 0.17.5 (2021-02-25)

### Bug fixes

Use Alt-l for the default `selectLine` binding, because Mod-l already has an important meaning in the browser.

Make `deleteGroupBackward`/`deleteGroupForward` delete groups of whitespace when bigger than a single space.

Don't change lines that have the end of a range selection directly at their start in `indentLess`, `indentMore`, and `indentSelection`.

## 0.17.4 (2021-02-18)

### Bug fixes

Fix a bug where `deleteToLineEnd` would delete the rest of the document when at the end of a line.

## 0.17.3 (2021-02-16)

### Bug fixes

Fix an issue where `insertNewlineAndIndent` behaved strangely with the cursor between brackets that sat on different lines.

## 0.17.2 (2021-01-22)

### New features

The new `insertTab` command inserts a tab when nothing is selected, and defers to `indentMore` otherwise.

The package now exports a `defaultTabBinding` object that provides a recommended binding for tab (if you must bind tab).

## 0.17.1 (2021-01-06)

### New features

The package now also exports a CommonJS module.

## 0.17.0 (2020-12-29)

### Breaking changes

First numbered release.

