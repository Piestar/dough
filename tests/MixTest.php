<?php

use Piestar\Dough\DoughMixer;

class MixTest extends PHPUnit_Framework_TestCase {

	public function testEscapedAndUnescapedMixing() {
		$data = [
			'type' => 'apple',
			'what' => '<pie>',
			'when' => '<now>',
		];
		$string = 'Eat more {{ type }} {{ what }} {!! when !!}';

		$this->assertEquals('Eat more apple &lt;pie&gt; <now>', DoughMixer::mix($string, $data));
	}

	public function testCanUseDotNotation() {
		$data= [
			'user' => [
				'type' => 'apple',
				'what' => '<pie>',
				'when' => '<now>',
			],
		];
		$string = 'Eat more {{ user.type }} {{ user.what }} {!! user.when !!}';

		$this->assertEquals('Eat more apple &lt;pie&gt; <now>', DoughMixer::mix($string, $data));
	}

	public function testSupportsHtmlable() {
		$data = [
			'type' => 'apple',
			'what' => new Htmlable('<pie>'),
			'when' => new Htmlable('<now>'),
		];
		$string = 'Eat more {{ type }} {{ what }} {!! when !!}';

		$this->assertEquals('Eat more apple <pie> <now>', DoughMixer::mix($string, $data));
	}

	public function testIgnoresMisformedTags() {
		$data = [
			'type' => 'apple',
			'what' => '<pie>',
			'when' => '<now>',
		];
		$string = 'Eat more { type }} {! what !!} {!! when !!}';

		$this->assertEquals('Eat more { type }} {! what !!} <now>', DoughMixer::mix($string, $data));
	}

	public function testIgnoresRottenDough() {
		$data = [
			'when' => '<now>',
		];
		$string = 'Eat more {{ type }} {!! what !!} {!! when !!}';

		$this->assertEquals('Eat more {{ type }} {!! what !!} <now>', DoughMixer::mix($string, $data));
	}

	public function testIgnoresNull() {
		$data = [
			'type' => null,
			'what' => '<pie>',
			'when' => '<now>',
		];
		$string = 'Eat more {{ type }} {{ what }} {!! when !!}';

		$this->assertEquals('Eat more  &lt;pie&gt; <now>', DoughMixer::mix($string, $data));
	}
}

class Htmlable {

	private $value;

	function __construct($value) {
		$this->value = $value;
	}

	function toHtml() {
		return $this->value;
	}

	function __toString() {
		return $this->value;
	}
}

