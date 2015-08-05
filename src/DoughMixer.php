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
 * Examples:
 *
 *   DoughMixer::mix("pie is {{ pie }}", ['pie' => '<good>']) => "pie is &lt;good&rt;"
 *   DoughMixer::mix("pie is {!! pie !!}", ['pie' => '']) => "pie is <good>"
 *   DoughMixer::mix("Eat {{ pie.name }}!", ['pie' => ['name' => 'Apple Pie']]) => "Eat Apple Pie!"
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
		$dough = DoughMixer::replace($dough, $ingredients, true); // Process {{ }}
		$dough = DoughMixer::replace($dough, $ingredients, false);  // Process {!! !!}
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
				$finalValue = self::array_get($ingredients, $match, self::ROTTEN_DOUGH);

				if ($finalValue === self::ROTTEN_DOUGH) {
					continue;
				}

				// To re-substitute, we currently just use another regex containing the originally matched pattern.
				$regex      = '/' . $matches[0][$index] . '/';
				$finalValue = $escape ? self::escape($finalValue) : $finalValue;
				$dough     = preg_replace($regex, $finalValue, $dough);
			}

		}

		return $dough;
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
		if (is_null($key)) {
			return $array;
		}

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

		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
}