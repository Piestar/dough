<?php namespace Piestar\Dough;

/**
 * This class is the parser for Dough.
 *
 * Dough is a templating language that understands two constructs:
 *
 *   Normal variables (will be HTML-escaped on output)
 *     {{ some_variable }}
 *
 *   Raw variables (will not be HTML-escaped when output)
 *     {!! some_variable !!}
 *
 * It also allows for arrays in its data:
 *     {{ pie.name }}
 *
 * A tag may declare an inline default with the `or` keyword and a quoted
 * string, using either quote style. The default is emitted when the path is
 * unresolved, null, or an empty string:
 *     {{ user.last_name or "Member" }}
 *     {!! user.link or '<a href="/">home</a>' !!}
 *
 * Examples:
 *
 *   DoughMixer::mix("pie is {{ pie }}", ['pie' => '<good>']) => "pie is &lt;good&gt;"
 *   DoughMixer::mix("pie is {!! pie !!}", ['pie' => '<good>']) => "pie is <good>"
 *   DoughMixer::mix("Eat {{ pie.name }}!", ['pie' => ['name' => 'Apple Pie']]) => "Eat Apple Pie!"
 *   DoughMixer::mix('Hi {{ name or "friend" }}', []) => "Hi friend"
 *
 */
class DoughMixer {

	const ROTTEN_DOUGH = '__ROTTEN-DOUGH__';

	/**
	 * @param string $dough The string template to process {{ }} and {!! !!} constructs inside of.
	 * @param array $ingredients An array of values that may be used in the template.
	 *
	 * @return string Returns $template with {{ }} and {!! !!} constructs replaced with their corresponding $data values.
	 */
	public static function mix($dough, $ingredients) {
		if (strpos($dough, '{') === false) {
			return $dough;
		}

		$dough = DoughMixer::replace($dough, $ingredients, true);  // Process {{ }}
		$dough = DoughMixer::replace($dough, $ingredients, false); // Process {!! !!}
		return $dough;
	}

	/**
	 * @param string $dough
	 * @param array $ingredients
	 * @param bool $escape
	 *
	 * @return mixed
	 */
	protected static function replace ($dough, $ingredients, $escape) {

		$pattern = $escape ? '/{{ *(.+?) *}}/' : '/{!! *(.+?) *!!}/';

		if (preg_match_all($pattern, $dough, $matches)) {

			foreach ($matches[1] as $index => $match) {
				list($path, $default, $hasDefault) = self::parseDefault($match);

				$finalValue = self::array_get($ingredients, $path, self::ROTTEN_DOUGH);

				if ($hasDefault) {
					if ($finalValue === self::ROTTEN_DOUGH || $finalValue === null || $finalValue === '') {
						$finalValue = $default;
					}
				} else if ($finalValue === self::ROTTEN_DOUGH) {
					continue;
				}

				$finalValue = $escape ? self::escape($finalValue) : $finalValue;
				$dough      = str_replace($matches[0][$index], $finalValue, $dough);
			}

		}

		return $dough;
	}

	/**
	 * Splits a tag expression into its path and optional inline default.
	 *
	 * A default is written with the `or` keyword followed by a quoted string,
	 * e.g. {{ user.name or "Member" }}. Either quote style is accepted, and the
	 * quoted value may contain spaces.
	 *
	 * @param string $expression
	 *
	 * @return array [string $path, string|null $default, bool $hasDefault]
	 */
	protected static function parseDefault($expression)
	{
		if (preg_match('/^(.*?)\s+or\s+([\'"])(.*)\2$/s', $expression, $matches)) {
			return [trim($matches[1]), $matches[3], true];
		}

		return [$expression, null, false];
	}

	/**
	 * Copied from also-MIT-licensed Illuminate\Support\Arr to eliminate dependency.
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed|\Closure $default
	 *
	 * @return mixed
	 */
	protected static function array_get($array, $key, $default = null)
	{
		if (isset($array[$key])) {
			return $array[$key];
		}

		foreach (explode('.', $key) as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return $default instanceof \Closure ? $default() : $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	protected static function escape($value)
	{
		if (is_object($value) && method_exists($value, 'toHtml')) { // Support Illuminate\Contracts\Support\Htmlable
			return $value->toHtml();
		}

		return htmlentities((string) $value, ENT_QUOTES, 'UTF-8', false);
	}
}