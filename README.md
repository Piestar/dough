# Dough Template Language

[![Build Status](https://travis-ci.org/Piestar/dough.svg?branch=master)](https://travis-ci.org/Piestar/dough)
[![Total Downloads](https://poser.pugx.org/Piestar/dough/d/total.svg)](https://packagist.org/packages/Piestar/dough)
[![Latest Version](https://poser.pugx.org/Piestar/dough/v/stable.svg)](https://packagist.org/packages/Piestar/dough)
[![License](https://poser.pugx.org/Piestar/dough/license.svg)](https://packagist.org/packages/Piestar/dough)

Dough is a tiny templating language that understands two constructs:

* `{{ some_variable }}` Normal variables (will be HTML-escaped on output)

* `{!! some_variable !!}` Raw variables (will not be HTML-escaped when output)

It also allows for arrays in its data:
    `{{ pie.name }}`

We use this for user-exposed tokens in a mail merge, where we wouldn't want the user to have access to a more complex
templating language with a larger surface area to secure.

Be aware that this package does not currently protect against JavaScript or malicious HTML injection.
    
## Examples
```php
$mixed = DoughMixer::mix("pie is {{ pie }}"   , ['pie' => '<good>']); // "pie is &lt;good&rt;"
$mixed = DoughMixer::mix("pie is {!! pie !!}" , ['pie' => '<good>']); // "pie is <good>"
$mixed = DoughMixer::mix("Eat {{ pie.name }}!", 
                                 ['pie' => ['name' => 'Apple Pie']]); // "Eat Apple Pie!"
```
