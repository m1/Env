<?php

namespace M1\Env\Test;

use \M1\Env\Parser;
use \M1\Env\Exception\ParseException;

class EnvTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleEnv()
    {
        $expected = array(
            'TK1' => 'value',
            'TK2' => 'value',
            'TK3' => 'value',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/simple.env'));
        $this->assertSame($expected, $env);
    }

    public function testDoubleQuotedEnv()
    {
        $expected = array(
            'TK1' => 'value',
            'TK2' => 'value " value',
            'TK3' => 'value "value" value',
            'TK4' => 'value value',
            'TK5' => "value \n value",
            'TK6' => "value 'value' value",
            'TK7' => "value \n 'value', \"value\" value",
            'TK8' => "",
            'TK9' => 'value',
            'TK10' => 'value "value"',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/double_quoted.env'));
        $this->assertEquals($expected, $env);
    }

    public function testSingleQuotedEnv()
    {
        $expected = array(
            'TK1' => 'value',
            'TK2' => 'value \' value',
            'TK3' => 'value \'value\' value',
            'TK4' => 'value value',
            'TK5' => "value \n value",
            'TK6' => 'value \'value\' value',
            'TK7' => "value \n \"value\", 'value' value",
            'TK8' => '',
            'TK9' => 'value',
            'TK10' => 'value \'value\'',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/single_quoted.env'));
        $this->assertSame($expected, $env);
    }

    public function testBoolEnv()
    {
        $expected = array(
            'TK1' => true,
            'TK2' => false,
            'TK3' => true,
            'TK4' => false,
            'TK5' => true,
            'TK6' => false,
            'TK7' => true,
            'TK8' => false,
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/bool.env'));
        $this->assertSame($expected, $env);
    }

    public function testNumbersEnv()
    {
        $expected = array(
            'TK1' => 1,
            'TK2' => 1.1,
            'TK3' => "33 33",
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/numbers.env'));
        $this->assertSame($expected, $env);
    }

    public function testNullEnv()
    {
        $expected = array(
            'TK1' => null,
            'TK2' => null,
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/null.env'));
        $this->assertSame($expected, $env);
    }

    public function testCommentsEnv()
    {
        $expected = array(
            'TK1' => 'value',
            'TK2' => 'value',
            'TK3' => 'value#thisisnotacomment',
            'TK4' => null
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/comments.env'));
        $this->assertSame($expected, $env);
    }

    public function testEmptyFileEnv()
    {
        $expected = array(
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/empty_file.env'));
        $this->assertSame($expected, $env);
    }

    public function testAllWithVariablesEnv()
    {
        $expected = array(
            'double_1' => "hey\nhey",
            'double_2' => "hello \"hello\" hello",
            'double_3' => "hello \"hello\" hello",
            'single_1' => 'hey',
            'single_2' => 'hey there!',
            'single_3' => 'hey there with escaped \' !',
            'single_4' => 'hey there with unescaped " !',
            'tab_test_1' => "hi\ttab",
            'unquoted_1' => 'hello',
            'unquoted_2' => 'hey2',
            'int_1' => 1,
            'int_2' => 2,
            'float_1' => 3.14,
            'un_1' => 0,
            'un_2' => "1",
            'un_3' => "0",
            'bool_1' => true,
            'bool_2' => true,
            'test_variable_1' => 'hey',
            'test_variable_2' => "hey there!/hey\nhey",
            'test_variable_3' => "hey\nhey/hey",
            'test_variable_4' => 'hello hello "hello" hellohey there with unescaped " !',
            'test_variable_5' => "true",
            'test_variable_6' => true,
            'test_variable_7' => "true/true",
            'test_variable_8' => '${bool_1}',
            'test_variable_9' => "1",
            'test_variable_10' => "3.14/1",
            'test_variable_11' => "2/1",
            'test_variable_12' => "1 3.14",
            'test_variable_13' => 1,
            'test_variable_14' => 3.14,
            'test_variable_15' => "hello 3.14",
            'test_variable_16' => 'hey there with escaped \' !',
            'test_variable_17' => 'hello "hello" hello hey there with escaped \' !',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/variables.env'));
        $this->assertEquals($expected, $env);
    }

    public function testVariableParameterExpansion()
    {
        $expected = array(
            "TK1" => "TV1",
            "TK2" => "TV1",
            "TK3" => "TV3",
            "TK4" => "TV4",
            "TK5" => null,
            "TK6" => null,
            "TK7" => null,
            "TK8" => null,
            "TK_1" => "TV3",
            'TK_3' => '',
            'TK9' => '',
            'TK_4' => null,
            'TK10' => null,
            'TK_5' => true,
            'TK11' => true,
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/variable_parameter_expansion.env'));
        $this->assertEquals($expected, $env);
    }

    public function testFinalCase()
    {
        $expected = array(
            "TEST1" => "value",
            "TEST2" => "value",
            "TEST3" => "value",
            "TEST4" => "value",
            "TEST5" => "value value",
            "TEST6" => "value \n value",
            "TEST7" => 'value "value" value',
            "TEST8" => "value",
            "TEST9" => "value ' value ' value",
            "TEST10" => 1,
            "TEST11" => 1.1,
            "TEST12" => "33 33",
            "TEST13" => "33",
            "TEST14" => true,
            "TEST15" => false,
            "TEST16" => true,
            "TEST17" => false,
            "TEST18" => true,
            "TEST19" => false,
            "TEST20" => true,
            "TEST21" => false,
            "TEST22" => "true",
            "TEST23" => "YES",
            "TEST24" => 'NO',
            "TEST25" => null,
            "TEST26" => null,
            "TEST27" => "hello",
            "TEST28" => "hello",
            "TEST29" => 1,
            "TEST30" => 2,
            "TEST31" => 1,
            "TEST32" => "1",
            "TEST33" => "1 2",
            "TEST34" => "foo",
            "TEST35" => "bar",
            "TEST36" => "foo/bar",
            "TEST37" => "foo",
            "TEST38" => 'bar',
            "TEST39" => "hello foo and bar",
            "TEST40" => true,
            "TEST41" => false,
            "TEST42" => true,
            "TEST43" => "true false",
            "TEST44" => null,
            "TEST45" => null,
            "TEST46" => "",
            'TEST47' => 'foo',
            'TEST48' => 'foo',
            'TEST50' => 'foo',
            'TEST49' => 'foo',
            'TEST51' => 'foo',
            'TEST53' => null,
            'TEST54' => null,
            'TEST55' => null,
            'TEST56' => null,
            'TEST58' => '',
            'TEST57' => '',
            'TEST60' => null,
            'TEST59' => null,
            'TEST62' => true,
            'TEST61' => true,
            'TEST63' => 'hello',
            'TEST64' => 'hello # comment',
            'TEST65' => 'hello',
            'TEST66' => null,
            'TEST67' => '#comment',
            'TEST68' => 'thisisnota#comment',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/all_testcase.env'));
        $this->assertEquals($expected, $env);
    }

    public function testOtherEnv()
    {
        $expected = array(
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/other.env'));
        $this->assertSame($expected, $env);
    }

    public function testNullVariable()
    {
        $expected = array(
            'test_key_1' => null,
            'test_key_2' => null,
            'test_key_3' => ""
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/null_variable.env'));
        $this->assertSame($expected, $env);
    }

    public function testExport()
    {
        $expected = array(
            'TK1' => 'value',
            'TK2' => 'value',
            'TK3' => 'value',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/export.env'));
        $this->assertSame($expected, $env);
    }

    public function testContextVariables()
    {
        $expected = array(
            'TK1' => 'external',
            'TK2' => 'external',
            'TK3' => 'TK2',
            'TK4' => 'TK2-external',
        );

        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/context_variables.env'), array('EXTERNAL' => 'external'));
        $this->assertSame($expected, $env);
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testInvalidKey()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_invalid_key.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testInvalidKeyNumber()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_invalid_key_number.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testOnlyKey()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_only_key.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testUndefinedVariable()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_undefined_variable.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testInvalidParameterExpansion()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_invalid_parameter_expansion.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testMissingSingleQuote()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_missing_single_quote.env'));
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testInvalidExport()
    {
        $env = Parser::Parse(file_get_contents(__DIR__.'/mocks/fail_export.env'));
    }
}
