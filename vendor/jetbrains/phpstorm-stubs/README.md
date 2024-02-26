# phpstorm-stubs 

[![official JetBrains project](http://jb.gg/badges/official.svg)](https://confluence.jetbrains.com/display/ALL/JetBrains+on+GitHub) 
[![Build Status](https://travis-ci.org/JetBrains/phpstorm-stubs.svg?branch=master)](https://travis-ci.org/JetBrains/phpstorm-stubs)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://www.apache.org/licenses/LICENSE-2.0.html)
[![Total Downloads](https://poser.pugx.org/jetbrains/phpstorm-stubs/downloads)](https://packagist.org/packages/jetbrains/phpstorm-stubs)

STUBS are normal, syntactically correct PHP files that contain function & class signatures, constant definitions, etc. for all built-in PHP stuff and most standard extensions. Stubs need to include complete [PHPDOC], especially proper @return annotations.

An IDE needs them for completion, code inspection, type inference, doc popups, etc. Quality of most of these services depend on the quality of the stubs (basically their PHPDOC @annotations).

Note that the stubs for “non-standard” extensions are provided as is. (Non-Standard extensions are the ones that are not part of PHP Core or are not Bundled/External - see the complete list [here](http://php.net/manual/en/extensions.membership.php).)

The support for such “non-standard” stubs is community-driven, and we only validate their PHPDoc. We do not check whether a stub matches the actual extension or whether the provided descriptions are correct.

[Relevant open issues]

### Contribution process
[Contribution process](CONTRIBUTING.md)

### Updating the IDE
Have a full copy of the .git repo within an IDE and provide its path in `Settings | Languages & Frameworks | PHP | PHP Runtime | Advanced settings | Default stubs path`. It should then be easily updatable both ways via normal git methods.

### Extensions enabled by default
The set of extensions enabled by default in PhpStorm can change anytime without prior notice. To learn how to view the enabled extensions, look [here](https://blog.jetbrains.com/phpstorm/2017/03/per-project-php-extension-settings-in-phpstorm-2017-1/).

### How to run tests
1. Execute `docker-compose -f docker-compose.yml run php composer install -d /opt/project/phpstorm-stubs --ignore-platform-reqs`
2. Execute `docker-compose -f docker-compose.yml run php /opt/project/phpstorm-stubs/vendor/bin/phpunit /opt/project/phpstorm-stubs/tests/`

### How to update stub map
Execute `docker-compose -f docker-compose.yml run php /usr/local/bin/php /opt/project/phpstorm-stubs/generate-stub-map` and commit the resulting `PhpStormStubsMap.php`

### License
[Apache 2]

contains material by the PHP Documentation Group, licensed with [CC-BY 3.0] 

[PHPDOC]:https://github.com/phpDocumentor/fig-standards/blob/master/proposed/phpdoc.md
[Apache 2]:https://www.apache.org/licenses/LICENSE-2.0
[Relevant open issues]:https://youtrack.jetbrains.com/issues/WI?q=%23Unresolved+Subsystem%3A+%7BPHP+lib+stubs%7D+order+by%3A+votes+
[CC-BY 3.0]:https://www.php.net/manual/en/cc.license.php