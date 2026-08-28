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

	public function testUsesResolvedValueOverDefault() {
		$this->assertEquals('Smith', DoughMixer::mix('{{ user.last_name???="Member" }}', ['user' => ['last_name' => 'Smith']]));
	}

	public function testDefaultWhenMissing() {
		$this->assertEquals('Member', DoughMixer::mix('{{ user.last_name???="Member" }}', ['user' => []]));
	}

	public function testDefaultWhenNull() {
		$this->assertEquals('Member', DoughMixer::mix('{{ user.last_name???="Member" }}', ['user' => ['last_name' => null]]));
	}

	public function testDefaultWhenEmptyString() {
		$this->assertEquals('Member', DoughMixer::mix('{{ user.last_name???="Member" }}', ['user' => ['last_name' => '']]));
	}

	public function testKeepsZeroValue() {
		$this->assertEquals('0', DoughMixer::mix('{{ count???="none" }}', ['count' => '0']));
	}

	public function testDefaultAcceptsSingleQuotes() {
		$this->assertEquals('Member', DoughMixer::mix("{{ user.last_name???='Member' }}", ['user' => []]));
	}

	public function testDefaultDelimiterWithoutEquals() {
		$this->assertEquals('Member', DoughMixer::mix('{{ user.last_name???"Member" }}', ['user' => []]));
	}

	public function testDefaultMayContainSpaces() {
		$this->assertEquals('Valued Member', DoughMixer::mix('{{ user.last_name???="Valued Member" }}', ['user' => []]));
	}

	public function testDefaultIsEscapedInEscapedTag() {
		$this->assertEquals('A &amp; B', DoughMixer::mix('{{ x???="A & B" }}', []));
	}

	public function testDefaultIsRawInRawTag() {
		$this->assertEquals('<b>Member</b>', DoughMixer::mix("{!! x???='<b>Member</b>' !!}", []));
	}

	public function testResolvesDefaultOnDeepPath() {
		$this->assertEquals('deep', DoughMixer::mix('{{ a.b.c???="x" }}', ['a' => ['b' => ['c' => 'deep']]]));
		$this->assertEquals('x', DoughMixer::mix('{{ a.b.c???="x" }}', ['a' => ['b' => []]]));
	}

	public function testMixesDefaultAlongsideOrdinaryTags() {
		$string = 'Dear {{ user.last_name???="Member" }} ({{ user.first_name }})';
		$this->assertEquals('Dear Member (Jo)', DoughMixer::mix($string, ['user' => ['first_name' => 'Jo']]));
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

