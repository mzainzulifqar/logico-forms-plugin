<?php

namespace Logicoforms\Forms\Tests\Unit;

use Logicoforms\Forms\Support\OptionValue;
use Logicoforms\Forms\Tests\TestCase;

class OptionValueTest extends TestCase
{
    public function test_converts_label_to_kebab_case(): void
    {
        $this->assertEquals('very_likely', OptionValue::fromLabel('Very likely'));
        $this->assertEquals('not_at_all', OptionValue::fromLabel('Not at all'));
    }

    public function test_handles_special_characters(): void
    {
        $this->assertEquals('hello_world', OptionValue::fromLabel('Hello & World!'));
        $this->assertEquals('100_satisfied', OptionValue::fromLabel('100% Satisfied'));
        $this->assertEquals('option_1', OptionValue::fromLabel('  Option #1  '));
    }

    public function test_handles_whitespace_and_multiple_underscores(): void
    {
        $this->assertEquals('too_many_spaces', OptionValue::fromLabel('Too   Many   Spaces'));
        $this->assertEquals('leading_trailing', OptionValue::fromLabel('  Leading & Trailing  '));
    }
}
