<?php

namespace M1\Env\Test;

use \M1\Env\Env;
use \M1\Env\Exception\ParseException;

class EnvTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleEnv()
    {
        $excepted = array(
            'TK1' => 'value',
            'TK2' => 'value',
            'TK3' => 'value',
        );

        $env = Env::Parse(__DIR__.'/mocks/simple.env');
        $this->assertSame($excepted, $env);
    }

    public function testDoubleQuotedEnv()
    {

        $excepted = array(
            'TK1' => 'value',
            'TK2' => 'value " value',
            'TK3' => 'value "value" value',
            'TK4' => 'value value',
            'TK5' => "value \n value",
            'TK6' => "value 'value' value",
            'TK7' => "value \n 'value', \"value\" value",
            'TK8' => "",
            'TK9' => 'value',
        );

        $env = Env::Parse(__DIR__.'/mocks/double_quoted.env');
        $this->assertEquals($excepted, $env);
    }

    public function testSingleQuotedEnv()
    {
        $excepted = array(
            'TK1' => 'value',
            'TK2' => 'value \' value',
            'TK3' => 'value \'value\' value',
            'TK4' => 'value value',
            'TK5' => "value \n value",
            'TK6' => 'value \'value\' value',
            'TK7' => "value \n \"value\", 'value' value",
            'TK8' => '',
            'TK9' => 'value',
        );

        $env = Env::Parse(__DIR__.'/mocks/single_quoted.env');
        $this->assertSame($excepted, $env);
    }

    public function testBoolEnv()
    {
        $excepted = array(
            'TK1' => true,
            'TK2' => false,
            'TK3' => true,
            'TK4' => false,
            'TK5' => true,
            'TK6' => false,
            'TK7' => true,
            'TK8' => false,
        );

        $env = Env::Parse(__DIR__.'/mocks/bool.env');
        $this->assertSame($excepted, $env);
    }

    public function testNumbersEnv()
    {
        $excepted = array(
            'TK1' => 1,
            'TK2' => 1.1,
            'TK3' => "33 33",
        );

        $env = Env::Parse(__DIR__.'/mocks/numbers.env');
        $this->assertSame($excepted, $env);
    }

    public function testNullEnv()
    {
        $excepted = array(
            'TK1' => null,
            'TK2' => null,
        );

        $env = Env::Parse(__DIR__.'/mocks/null.env');
        $this->assertSame($excepted, $env);
    }

    public function testCommentsEnv()
    {
        $excepted = array(
            'TK1' => 'value',
            'TK2' => 'value',
            'TK3' => 'value',
            'TK4' => null
        );

        $env = Env::Parse(__DIR__.'/mocks/comments.env');
        $this->assertSame($excepted, $env);
    }

    public function testEmptyFileEnv()
    {
        $excepted = array(
        );

        $env = Env::Parse(__DIR__.'/mocks/empty_file.env');
        $this->assertSame($excepted, $env);
    }

    public function testAllWithVariablesEnv()
    {
        $excepted = array(
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
            'test_variable_8' => 'true',
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

        $env = Env::Parse(__DIR__.'/mocks/variables.env');
        $this->assertEquals($excepted, $env);
    }

    public function testOtherEnv()
    {
        $excepted = array(
        );

        $env = Env::Parse(__DIR__.'/mocks/other.env');
        $this->assertSame($excepted, $env);
    }

    public function testParseOriginException()
    {
        try {
            $env = Env::Parse(__DIR__.'/mocks/fail_simple.env', true);
        } catch (ParseException $e) {
            $this->assertEquals(__DIR__ . '/mocks/fail_simple.env', $e->getFile());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotAFile()
    {
        $env = Env::Parse(__DIR__.'/mocks/NONE.env');
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testInvalidKey()
    {
        $env = Env::Parse(__DIR__.'/mocks/fail_invalid_key.env');
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testOnlyKey()
    {
        $env = Env::Parse(__DIR__.'/mocks/fail_only_key.env');
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testUndefinedVariable()
    {
        $env = Env::Parse(__DIR__.'/mocks/fail_undefined_variable.env');
    }

    /**
     * @expectedException \M1\Env\Exception\ParseException
     */
    public function testMissingSingleQuote()
    {
        $env = Env::Parse(__DIR__.'/mocks/fail_missing_single_quote.env');
    }
}
