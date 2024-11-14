# SQLParser - Parse MySQL schemas in PHP, fast

[![Build Status](https://github.com/iamcal/SQLParser/actions/workflows/php.yml/badge.svg)](https://github.com/iamcal/SQLParser/actions)
[![Coverage Status](https://coveralls.io/repos/github/iamcal/SQLParser/badge.svg?branch=master)](https://coveralls.io/github/iamcal/SQLParser?branch=master)

This library takes MySQL `CREATE TABLE` statements and returns a data structure representing the table that it defines.
MySQL syntax [version 5.7](https://dev.mysql.com/doc/refman/5.7/en/create-table.html) is supported.
This library does not try to validate input - the goal is to deconstruct valid `CREATE TABLE` statements.

## Installation

You can install this package using composer. To add it to your `composer.json`:

```plain
composer require iamcal/sql-parser
```

You can then load it using the composer autoloader:

```php
require_once 'vendor/autoload.php';
use iamcal\SQLParser;

$parser = new SQLParser();
```

If you don't use composer, you can skip the autoloader and include `src/SQLParser.php` directly.

## Usage

To extract the tables defined in SQL:

```php
$parser = new SQLParser();
$parser->parse($sql);

print_r($parser->tables);
```

The `tables` property is an array of tables, each of which is a nested array structure defining the
table's structure:

```SQL
CREATE TABLE `achievements_counts` (
  `achievement_id` int(10) unsigned NOT NULL,
  `num_players` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

```php
[
    'achievements_counts' => [
        'name' => 'achievements_counts',
        'fields' => [
            [
                'name' => 'achievement_id',
                'type' => 'INT',
                'length' => '10',
                'unsigned' => true,
                'null' => false,
            ],
            [
                'name' => 'num_players',
                'type' => 'INT',
                'length' => '10',
                'unsigned' => true,
                'null' => false,
            ],
            [
                'name' => 'date_updated',
                'type' => 'INT',
                'length' => '10',
                'unsigned' => true,
                'null' => false,
            ],
        ],
        'indexes' => [
            [
                'type' => 'PRIMARY',
                'cols' => [
                    [
                        'name' => 'achievement_id',
                    ],
                ],
            ],
        ],
        'props' => [
            'ENGINE' => 'InnoDB',
            'CHARSET' => 'utf8',
        ],
    ],
]
```

You can also use the lexer directly to work with other piece of SQL:

```php
$parser = new SQLParser();
$parser->lex($sql);

print($parser->tokens);
```

The `tokens` property contains an array of tokens. SQL keywords are returned as uppercase,
with multi-word terms (e.g. `DEFAULT CHARACTER SET`) as a single token. Strings and escaped
identifiers are not further processed; they are returned exactly as expressed in the input SQL.

By default, the tokenizer will ignore unterminated comments and strings, and stop parsing at
that point, producing no further tokens. You can set `$parser->throw_on_bad_syntax = true;` to
throw an exception of type `iamcal\SQLParserSyntaxException` instead.

## Performance

My test target is an 88K SQL file containing 114 tables from Glitch's main database.

The first version, using [php-sql-parser](http://code.google.com/p/php-sql-parser/), took over 60
seconds just to lex the input. This was obviously not a great option.

The current implementation uses a hand-written lexer which takes around 140ms to lex the same
input and imposes less odd restrictions. This seems to be the way to go.

## History

This library was created to parse multiple `CREATE TABLE` schemas and compare them, so
figure out what needs to be done to migrate one to the other.

This is based on the system used at b3ta, Flickr and then Tiny Speck to check the differences
between production and development databases and between shard instances. The original system
just showed a diff (see [SchemaDiff](https://github.com/iamcal/SchemaDiff)), but that was a bit
of a pain.

## Unsupported features

MySQL table definitions have a *lot* of options, so some things just aren't supported. They include:

* `UNION` table properties
* `TABLESPACE` table properties
* table partitions
* `FLOAT[(bits)]` fields
* Deprecated `YEAR(2|4)` fields
* `ASCII` attribute as a shorthand for `CHARACTER SET latin1`
* `UNICODE` attribute as a shorthand for `CHARACTER SET ucs2`
* `NATIONAL` modified for `CHAR` and `VARCHAR` fields

If you need support for one of these features, open an issue or (better) send a pull request with tests.

The specs for each of the four field groupings can be found here:

* https://dev.mysql.com/doc/refman/5.7/en/numeric-type-syntax.html
* https://dev.mysql.com/doc/refman/5.7/en/date-and-time-type-syntax.html
* https://dev.mysql.com/doc/refman/5.7/en/string-type-syntax.html
* https://dev.mysql.com/doc/refman/5.7/en/spatial-type-overview.html

## Alternatives

If you're using PHP, then [Modyllic](https://github.com/onlinebuddies/modyllic) is a great SQL parser and set of schema management tools.

If you're using Hack, then [Hack SQL Fake](https://github.com/slackhq/hack-sql-fake) allows you to parse SQL and create a fake MySQL
server for testing, with many (but not all!) features of MySQL.

## Publishing

To publish a new version:

* Commit changes to master
* Add git tag
* Go to https://packagist.org/packages/iamcal/sql-parser and hit "Update"
