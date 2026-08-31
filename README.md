# Dough

[![Tests](https://github.com/Piestar/dough/actions/workflows/tests.yml/badge.svg)](https://github.com/Piestar/dough/actions/workflows/tests.yml)
[![Latest Version](https://poser.pugx.org/piestar/dough/v/stable.svg)](https://packagist.org/packages/piestar/dough)
[![Total Downloads](https://poser.pugx.org/piestar/dough/d/total.svg)](https://packagist.org/packages/piestar/dough)
[![License](https://poser.pugx.org/piestar/dough/license.svg)](https://packagist.org/packages/piestar/dough)

Dough is a very small templating language for strings written by your users rather than by your developers.

Mail merges are the motivating case. When a non-technical user composes an email and drops `{{ first_name }}` into it, handing that string to a general-purpose template engine gives them a large and difficult-to-secure surface area. Dough gives them placeholders and nothing else: no loops, no conditionals, no includes, no function calls, no arbitrary code.

## Installation

```bash
composer require piestar/dough
```

No runtime dependencies.

Supported on PHP 8.2 and newer, which is what CI covers. The code itself carries
no dependencies and runs on PHP 5.4 and up, so it will install on older
runtimes, but those are untested and unsupported.

## Usage

```php
use Piestar\Dough\DoughMixer;

DoughMixer::mix('Eat more {{ pie }}', ['pie' => 'apple']); // "Eat more apple"
```

### Escaped and raw placeholders

`{{ }}` escapes its value on output. `{!! !!}` does not.

```php
DoughMixer::mix('pie is {{ pie }}',   ['pie' => '<good>']); // "pie is &lt;good&gt;"
DoughMixer::mix('pie is {!! pie !!}', ['pie' => '<good>']); // "pie is <good>"
```

Objects implementing a `toHtml()` method (such as Laravel's `Illuminate\Contracts\Support\Htmlable`) are rendered via that method instead of being escaped.

### Nested data

Reach into nested arrays with dot notation.

```php
DoughMixer::mix('Eat {{ pie.name }}!', ['pie' => ['name' => 'Apple Pie']]); // "Eat Apple Pie!"
```

### Default values

Follow a path with `or` and a quoted string to supply a fallback. The default is used when the path is unresolved, `null`, or an empty string. Either quote style works, and the default may contain spaces.

```php
DoughMixer::mix('Hi {{ user.name or "Member" }}',   ['user' => []]);                  // "Hi Member"
DoughMixer::mix('Hi {{ user.name or "Member" }}',   ['user' => ['name' => null]]);    // "Hi Member"
DoughMixer::mix('Hi {{ user.name or "Member" }}',   ['user' => ['name' => 'Smith']]); // "Hi Smith"
DoughMixer::mix("{!! greeting or '<b>Member</b>' !!}", []);                            // "<b>Member</b>"
DoughMixer::mix('{{ x or "A & B" }}', []);                                            // "A &amp; B"
```

A default is escaped or left raw according to the tag that contains it, exactly like a resolved value. A value of `'0'` is kept rather than treated as empty.

### Unresolved placeholders

A placeholder with no matching data and no default is left in the output verbatim rather than replaced with an empty string.

```php
DoughMixer::mix('Eat more {{ type }}', []); // "Eat more {{ type }}"
```

This is deliberate. In a mail merge, a visible `{{ type }}` tells the author they referenced something that does not exist; a silent blank does not.

## Security

Dough escapes with `htmlentities()` using `ENT_QUOTES`. That is the whole of its output filtering.

It does **not** sanitize HTML or JavaScript, and `{!! !!}` performs no filtering at all by design. If the values you pass in are themselves untrusted, or if your users may write raw placeholders, run the output through a dedicated HTML sanitizer before displaying it.

## Testing

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
