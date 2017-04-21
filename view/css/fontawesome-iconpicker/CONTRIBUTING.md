## Submitting issues
For feature requests, suggestions or ideas, add `[SUGGESTION]` before the title of the issue, for anything else follow the following guidelines.

### Guidelines
- Try to reproduce your problem in a separated environment, like in JSFiddle, [here is a template for it](http://jsfiddle.net/itsjavi/6aLndfgq/), that you can fork in the same page.
- Before posting your issue, consider adding this information:
  * Expected behaviour: what should happen?
  * Actual behaviour: what happens instead?
  * Your context: Where it happens? In which browser and version (if applicable)?
  * Plugin version (and/or commit reference).
  * jQuery version you use and list of all other plugins/scripts you are using with this one and may cause some conflict.
  * A link to your JSFiddle (or similar tool) demo where you reproduced the problem
  
## Contributing to Source Code

Thanks for wanting to contribute source code to this project. That's great!

- Before starting developing the plugin, you need to run `npm install` and `bower install` inside the plugin folder.
- Before your commits run always `grunt` inside the plugin folders, to update the dist files (don't modify them manually).
- Do not change the plugin coding style.
- Check that the index.html demos aren't broken (modify if necessary).
- Test your code at least in Chrome, Firefox and IE >= 10.
