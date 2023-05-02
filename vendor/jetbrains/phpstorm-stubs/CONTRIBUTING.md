# Contribution process
Thank you, your help is most appreciated, and improves experience for everyone!
Please follow the guidelines to keep it simpler for both sides. Contact us if unsure or in case if you *have* to massively violate these guidelines)

# Notes on content
Please check our [issue tracker] for issues corresponding to the problem you're fixing with your pull requests. Create issue on [issue tracker] describing the problem if there doesn't exist. Please link pull request/commit messages to the issue.

## Code Style
* Please avoid any unnecessary changes eg. spacing, line endings, HTML formatting. Remember, these files are NOT for human consumption. We want to preserve meaningful history.
* Please try to match existing style for any particular file - formatting, spacing, naming conventions.
* Please add corresponding @since tags
* Please run `composer cs` to check the code style and `composer cs-fix` to fix it

## Tests
 * Please make sure that tests pass for your Pull Request. 
 * If necessary, please include changes to *mutedProblems.json*.

## Types of contribution
As of 2017.1 Preview we gladly accept all "non-standard" extensions and IDE get a UI for per-project configuration.
As of 2016.3 there is an [easy way to package your custom stubs/metadata] as a plugin.


[issue tracker]:https://youtrack.jetbrains.com/issues/WI?q=%23Unresolved+%23%7BPHP+lib+stubs%7D+
[easy way to package your custom stubs/metadata]:https://github.com/artspb/phpstorm-library-plugin
