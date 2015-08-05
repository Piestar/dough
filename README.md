## Dough Template Language

[![Build Status](https://travis-ci.org/Piestar/dough.svg?branch=master)](https://travis-ci.org/Piestar/dough)
[![Total Downloads](https://poser.pugx.org/Piestar/dough/d/total.svg)](https://packagist.org/packages/Piestar/dough)
[![Latest Version](https://poser.pugx.org/Piestar/dough/v/stable.svg)](https://packagist.org/packages/Piestar/dough)
[![License](https://poser.pugx.org/Piestar/dough/license.svg)](https://packagist.org/packages/Piestar/dough)

Dough is a templating language that understands two constructs:

Normal variables (will be HTML-escaped on output)
    {{ some_variable }}

Raw variables (will not be HTML-escaped when output)
    {!! some_variable !!}

It also allows for arrays in its data:
    {{ user.name }}
