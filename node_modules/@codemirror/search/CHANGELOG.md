## 6.0.0 (2022-06-08)

### Bug fixes

Don't crash when a custom search panel doesn't have a field named 'search'.

Make sure replacements are announced to screen readers.

## 0.20.1 (2022-04-22)

### New features

It is now possible to disable backslash escapes in search queries with the `literal` option.

## 0.20.0 (2022-04-20)

### Bug fixes

Make the `wholeWords` option to `highlightSelectionMatches` default to false, as intended.

## 0.19.10 (2022-04-04)

### Bug fixes

Make sure search matches are highlighted when scrolling new content into view.

## 0.19.9 (2022-03-03)

### New features

The selection-matching extension now accepts a `wholeWords` option that makes it only highlight matches that span a whole word. Add SearchQuery.getCursor

The `SearchQuery` class now has a `getCursor` method that allows external code to create a cursor for the query.

## 0.19.8 (2022-02-14)

### Bug fixes

Fix a bug that caused the search panel to start open when configuring a state with the `search()` extension.

## 0.19.7 (2022-02-14)

### Breaking changes

`searchConfig` is deprecated in favor of `search` (but will exist until next major release).

### New features

The new `search` function is now used to enable and configure the search extension.

## 0.19.6 (2022-01-27)

### Bug fixes

Make `selectNextOccurrence` scroll the newly selected range into view.

## 0.19.5 (2021-12-16)

### Breaking changes

The search option `matchCase` was renamed to `caseSensitive` (the old name will continue to work until the next breaking release).

### Bug fixes

`openSearchPanel` will now update the search query to the current selection even if the panel was already open.

### New features

Client code can now pass a custom search panel creation function in the search configuration.

The `getSearchQuery` function and `setSearchQuery` effect can now be used to inspect or change the current search query.

## 0.19.4 (2021-12-02)

### Bug fixes

The search panel will no longer show the replace interface when the editor is read-only.

## 0.19.3 (2021-11-22)

### Bug fixes

Add `userEvent` annotations to search and replace transactions.

Make sure the editor handles keys bound to `findNext`/`findPrevious` even when there are no matches, to avoid the browser's search interrupting users.

### New features

Add a `Symbol.iterator` property to the cursor types, so that they can be used with `for`/`of`.

## 0.19.2 (2021-09-16)

### Bug fixes

`selectNextOccurrence` will now only select partial words if the current main selection hold a partial word.

Explicitly set the button's type to prevent the browser from submitting forms wrapped around the editor.

## 0.19.1 (2021-09-06)

### Bug fixes

Make `highlightSelectionMatches` not produce overlapping decorations, since those tend to just get unreadable.

Make sure any existing search text is selected when opening the search panel. Add search config option to not match case when search panel is opened (#4)

### New features

The `searchConfig` function now takes a `matchCase` option that controls whether the search panel starts in case-sensitive mode.

## 0.19.0 (2021-08-11)

### Bug fixes

Make sure to prevent the native Mod-d behavior so that the editor doesn't lose focus after selecting past the last occurrence.

## 0.18.4 (2021-05-27)

### New features

Initialize the search query to the current selection, when there is one, when opening the search dialog.

Add a `searchConfig` function, supporting an option to put the search panel at the top of the editor.

## 0.18.3 (2021-05-18)

### Bug fixes

Fix a bug where the first search command in a new editor wouldn't properly open the panel.

### New features

New command `selectNextOccurrence` that selects the next occurrence of the selected word (bound to Mod-d in the search keymap).

## 0.18.2 (2021-03-19)

### Bug fixes

The search interface and cursor will no longer include overlapping matches (aligning with what all other editors are doing).

### New features

The package now exports a `RegExpCursor` which is a search cursor that matches regular expression patterns.

The search/replace interface now allows the user to use regular expressions.

The `SearchCursor` class now has a `nextOverlapping` method that includes matches that start inside the previous match.

Basic backslash escapes (\n, \r, \t, and \\) are now accepted in string search patterns in the UI.

## 0.18.1 (2021-03-15)

### Bug fixes

Fix an issue where entering an invalid input in the goto-line dialog would submit a form and reload the page.

## 0.18.0 (2021-03-03)

### Breaking changes

Update dependencies to 0.18.

## 0.17.1 (2021-01-06)

### New features

The package now also exports a CommonJS module.

## 0.17.0 (2020-12-29)

### Breaking changes

First numbered release.

