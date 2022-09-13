---
title: Parser
permalink: /
---
PHP's generators are a great way for building incremental parsers.

## Example

This simple parser parses a line delimited protocol and prints a message for each line. Instead of printing a message, you could also invoke a data callback.

```php
$parser = new Parser((function () {
    while (true) {
        $line = yield "\r\n";
        
        if (trim($line) === "") {
            continue;
        }
        
        print "New item: {$line}" . PHP_EOL;
    }
})());

for ($i = 0; $i < 100; $i++) {
    $parser->push("bar\r");
    $parser->push("\nfoo");
}
```

## Yield Behavior

You can either `yield` a `string` that's used as delimiter, an `integer` that's used as length, or `null` for consuming everything that's available.
