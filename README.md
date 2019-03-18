# PHP Diff Class

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/aa609edb-cdb1-45cf-ad51-afbdab48f6a1/mini.png)](https://insight.sensiolabs.com/projects/aa609edb-cdb1-45cf-ad51-afbdab48f6a1) [![Codacy Badge](https://api.codacy.com/project/badge/Grade/db5f8d57b1234502aeb852afc87e0dfe)](https://www.codacy.com/app/leet31337/php-diff)

[![Latest Version](https://img.shields.io/github/release/JBlond/php-diff.svg?style=flat-square&label=Release)](https://github.com/JBlond/php-diff/releases)

## Introduction

A comprehensive library for generating differences between
two hashable objects (strings or arrays). Generated differences can be
rendered in all of the standard formats including:
 * Unified
 * Context
 * Inline HTML
 * Side by Side HTML

The logic behind the core of the diff engine (ie, the sequence matcher)
is primarily based on the Python difflib package. The reason for doing
so is primarily because of its high degree of accuracy.


## Install

```
composer require jblond/php-diff
```

## Example Use

```PHP
<?php
// installed via composer
require 'vendor/autoload.php';

// or installed manual
require dirname(__FILE__).'/../lib/Autoloader.php';
new \jblond\Autoloader(); 

$a = explode("\n", file_get_contents(dirname(__FILE__).'/a.txt'));
$b = explode("\n", file_get_contents(dirname(__FILE__).'/b.txt'));
// Options for generating the diff
$options = array(
    //'ignoreWhitespace' => true,
    //'ignoreCase' => true,
);
// Initialize the diff class
$diff = new \jblond\Diff($a, $b, $options);

//choose renderer
$renderer = new \jblond\Diff\Renderer\Html\SideBySide(array(
    'title_a' => 'Custom title for OLD version',
    'title_b' => 'Custom title for NEW version',
));

//show it
echo $diff->Render($renderer);
```

### Example Output
A quick usage example can be found in the example/ directory and under
example.php.

![Example Image](readme.png "Example")

![Example 2 Image](readme2.png "Example2")

## Requirements

- PHP 7.1 or greater
- PHP Multibyte String 

## Merge files using jQuery

Xiphe has build a jQuery plugin with that you can merge the compared
files. Have a look at [jQuery-Merge-for-php-diff](https://github.com/Xiphe/jQuery-Merge-for-php-diff).

## Todo

 * Ability to ignore blank line changes
 * 3 way diff support
 
##  Contributors

Contributors since I forked the repo.

- maxxer
- Creris
- jfcherng

### License (BSD License)

see [License](LICENSE)

## tests

```BASH
composer run-script php_src
composer run-script php_test
```
