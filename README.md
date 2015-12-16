# Env

[![Author](http://img.shields.io/badge/author-@milescroxford-blue.svg?style=flat-square)](https://twitter.com/milescroxford)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Env is a lightweight library bringing .env file parser compatibility to PHP. In short - it enables you to read .env files with PHP.

Env aims to bring a unified parser for env together for PHP rather than having a few incomplete or buggy parsers written into other libraries. This library is not meant as a complete package for config loading like other libraries as this is out of scope for this library. If you need something like that check out [Vars](http://github.com/m1/Vars)

## Install

Via Composer

``` bash
$ composer require m1/env
```

## Usage

### Basic

test.env
```bash
TEST_1 = VALUE
```

example.php
``` php

//both examples return the same thing

// example 1 -- standard
$env = new M1\Env('test.env');
$arr = $env->getContents();

// example 2 -- statically
$arr = Env::parse('test.env');

var_dump($arr);
// [
//      "TEST_1" => "VALUE"
// ]
```

### .env examples

```bash
# Comments are done like this

# Standard key=value
KEY = value
KEY = value
KEY = value # You can also comment inline like this

# Strings
KEY = "value"

## The value of the below variable will be TK4 = "value value"
KEY = "value value" "this sentence in quotes will not be counted"

## Escape newline
KEY = "value \n value"

## Escape double quotes
KEY = "value \"value\" value"

## You can also exchange any of the above for single quotes, eg:
KEY = 'value'

# Numbers
KEY = 1
KEY = 1.1
KEY = 33 33 # Will output as a string -- not a number as two numbers are given

# Bools -- All of the below are valid booleans
KEY = true
KEY = false
KEY = yes
KEY = no

## Booleans are case-insensitive
KEY = True
KEY = False
KEY = YES
KEY = NO

# Null values
KEY =
KEY = null

# Variables
string_1 = 'hello'
test_variable_1 = ${string_1} # Hello

int_1 = 1
int_2 = 2
test_variable_1 = ${int_1}   # 1 -- `int` type
test_variable_2 = "${int_1}" # 1 -- `string` type
test_variable_2 = ${int_1} ${int_2} # 1 2 -- `string` type

string_1 = foo
string_2 = bar
test_variable_1 = ${string_1}/${string_2} # foo/bar -- `string` type

string_3 = "foo"
string_4 = 'bar'
test_variable_1 = "hello ${string_3} and ${string_4}" # hello foo and bar -- `string` type

bool_1 = true
bool_2 = false
test_variable_1 = ${bool_1} # true -- `bool` type
test_variable_2 = ${bool_1} ${bool_2} # true false -- `string` type
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email hello@milescroxford.com instead of using the issue tracker.

## Credits

- [m1][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/m1/Env.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/m1/Env/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/m1/Env.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/m1/Env.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/m1/Env.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/m1/Env
[link-travis]: https://travis-ci.org/m1/Env
[link-scrutinizer]: https://scrutinizer-ci.com/g/m1/Env/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/m1/Env
[link-downloads]: https://packagist.org/packages/m1/Env
[link-author]: https://github.com/m1
[link-contributors]: ../../contributors
