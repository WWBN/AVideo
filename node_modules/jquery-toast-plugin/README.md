# Jquery Toast Plugin

A plugin to show highly customizable notifications to the user. 

<img src="http://i.imgur.com/RRrb0KE.png" />

# How to use

- You can install the plugin via Bower:

    ```js
    bower install jquery-toast-plugin
    ```
    
    or via `npm`
    
    ```js
    npm install jquery-toast-plugin
    ```
    
    Or directly download the repository and place the content of `dist` wherever you can access them.
- Include the CSS and JS files.
- Simply do ```$.toast('Toast message to be shown')``` Of course it would be the world's simplest toast message but believe me **you can do a lot more** with the options.

# Demo
For some quick demos and a detailed documentation accompanied by the demos for each of the available options can be accessed through http://kamranahmed.info/toast

## Quick usage examples
**Simple textual toast**
```javascript
// Non sticky version
$.toast("Lorem ipsum dolor sit amet, consectetur adipisicing elit. Hic, consequuntur doloremque eveniet eius eaque dicta repudiandae illo ullam. Minima itaque sint magnam dolorum asperiores repudiandae dignissimos expedita, voluptatum vitae velit.")
// Sticky version
$.toast({
  text : "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Hic, consequuntur doloremque eveniet eius eaque dicta repudiandae illo ullam. Minima itaque sint magnam dolorum asperiores repudiandae dignissimos expedita, voluptatum vitae velit.",
  hideAfter : false
})
```

**Toast using HTML as a text**
```javascript
// Non sticky
$.toast("Let's test some HTML stuff... <a href='#'>github</a>")
// sticky
$.toast({
  text : "<strong>Remember!</strong> You can <span style='font-weight: bold; color:red;' class='horribly-styled'>always</span> introduce your own × HTML and <span style='font-size: 18px;'>CSS</span> in the toast.",
  hideAfter : false
})
```

**Unordered list elements as the text of toast using array**
```javascript
// Non sticky version
$.toast(["Ubuntu : One of it's kind", "Sublime Text : Productivity unleashed", "HeidiSQL : Just love it", "Github : Just Lovely"])
// Sticky version
$.toast({
  text : ["Ubuntu : One of it's kind", "Sublime Text : Productivity unleashed", "HeidiSQL : Just love it", "Github : Just Lovely"],
  hideAfter : false
})
```

**Changing the animations**
```javascript
$.toast({ 
  text : "Let's test some HTML stuff... <a href='#'>github</a>", 
  showHideTransition : 'slide'  // It can be plain, fade or slide
})
```

**Changing the formatting**
```javascript
$.toast({ 
  text : "Let's test some HTML stuff... <a href='#'>github</a>", 
  showHideTransition : 'slide',  // It can be plain, fade or slide
  bgColor : 'blue',              // Background color for toast
  textColor : '#eee',            // text color
  allowToastClose : false,       // Show the close button or not
  hideAfter : 5000,              // `false` to make it sticky or time in miliseconds to hide after
  stack : 5,                     // `fakse` to show one stack at a time count showing the number of toasts that can be shown at once
  textAlign : 'left',            // Alignment of text i.e. left, right, center
  position : 'bottom-left'       // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values to position the toast on page
})
```

**Resetting the toast**
```javascript
var myToast = $.toast('Some toast that needs to be removed.');
myToast.reset(); // remove the toast "Some toast that needs to be removed"
```
What if I want to reset all the toasts at once? You may ask. Well in that case, you can do the following:
```javascript
$.toast().reset('all');
```

**Updating the toast**
Suppose, you had shown some toast upon the page, a sticky toast for example and now you want to update the toast. You can do the following

```javascript
var myToast = $.toast({
  text : 'Some toast that needs to show the success message after the ajax call.',
  hideAfter : false,
  bgColor : '#E01A31'
});

window.setTimeout(function(){
  myToast.update({
    text : '<strong>Updated after a few seconds</strong>',
    bgColor : '#23B65D'
  });
}, 5000);
```
To learn more about how to use and customize it, head to <a href="http://kamranahmed.info/toast" target="_blank">http://kamranahmed.info/toast</a>. Also you can find a customizer there that will let you modify the look and feel of the toast however you like it.

<hr>

You can simply download the repo or if you are in rush the <a href="https://raw.githubusercontent.com/kamranahmedse/jquery-toast-plugin/master/jquery.toast.min.css" target="_blank">minified CSS</a> or <a href="https://raw.githubusercontent.com/kamranahmedse/jquery-toast-plugin/master/jquery.toast.css">non-minified CSS</a> can be found and <a href="https://raw.githubusercontent.com/kamranahmedse/jquery-toast-plugin/master/jquery.toast.min.js" target="_blank">minified JS</a> and <a href="https://raw.githubusercontent.com/kamranahmedse/jquery-toast-plugin/master/jquery.toast.js" target="_blank">non minified JS</a> can also be found.

# Features
<ul>
  <li>Show different types of toasts i.e. informational, warning, errors and success</li>
  <li>Custom <strong>toast background color</strong> and <strong>text color</strong></li>
  <li>Ability to <strong>hack the CSS</strong> to add your own thing</li>
  <li>
    <strong>Text can be</strong> provided in the form of
    <ul>
      <li><strong>Array</strong> (It's elements will be changed to an un ordered list)</li>
      <li><strong>Simple text</strong></li>
      <li><strong>HTML</strong></li>
    </ul>
  </li>
  <li><strong>Events support</strong> i.e. <code>beforeHide</code>, <code>afterHidden</code>, <code>beforeShow</code>, <code>afterShown</code></li>

  <li><code>Fade</code> and <code>Slide</code> show/hide transitions support (More to come)</li>
  <li>Supports showing the loader for the toast</li>
  <li>You can <strong>position the toast, wherever you want</strong> there is support for <code>top-left</code>, <code>top-right</code> <code>bottom-left</code> and <strong>bottom-right</strong>, <code>top-center</code>, <code>bottom-center</code> and <code>mid-center</code> ...sighs! That's a whole lot of options, isn't it? No, you say. Ok then here is the most exciting thing, you can also introduce <strong>your own positioning</strong> just <strong>by passing a simple js object</strong> containing <code>{ top: - , bottom: -, left: -, right: - }</code> </li>

  <li>Ability to add <strong>sticky toast</strong></li>

  <li>Optional <strong>stack length can be defined</strong> (i.e. maximum number of toasts that can be shown at once)</li>

</ul>

Please report any bugs or features you would like added.

# Copyright

MIT © [Kamran Ahmed](http://kamranahmed.info)
