<?php

namespace Cybits;

use Cybits\Elbish\Plugin\Loader;

class PluginLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider codeProvider
     */
    public function testValidLoader($code, $classes)
    {
        $directory = sys_get_temp_dir() . "/test_" . time();

        @mkdir($directory);
        $fileName = $directory . '/test.php';
        file_put_contents($fileName, $code);
        $loader = new Loader();

        $this->assertEquals(
            $loader->loadPlugins($directory),
            array ($fileName => $classes)
        );
        @unlink($fileName);
        rmdir($directory);
    }


    public function codeProvider()
    {
        return array (
            array("<?php class TestClass{} ", array("\\TestClass")),
            array("<?php namespace Test; class TestClass{} ", array("\\Test\\TestClass")),
            array("<?php namespace Test { class TestClass{} }", array("\\Test\\TestClass")),
            array("<?php namespace Test; function x(){};", array()),
            array("<?php namespace Test; wrong!;", false),
            array("<?php namespace {class Test{}}
            namespace Test { class TestClass{} }", array("\\Test", "\\Test\\TestClass")),
        );
    }
}
