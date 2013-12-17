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

        $result = $loader->loadPlugins($directory);
        $this->assertArrayHasKey($fileName, $result);
        $this->assertEquals(1, count($result));
        if ($classes !== false) {
            foreach ($classes as $class) {
                $this->assertArrayHasKey($class, $result[$fileName]);
                $this->assertInstanceOf($class, $result[$fileName][$class]);
            }
        } else {
            $this->assertFalse($result[$fileName]);
        }

        @unlink($fileName);
        rmdir($directory);
    }


    public function codeProvider()
    {
        return array (
            array("<?php class TestClass{} ", array("\\TestClass")),
            array("<?php namespace Test; class TestClass{} ", array("\\Test\\TestClass")),
            array("<?php namespace Test { class AnotherTestClass{} }", array("\\Test\\AnotherTestClass")),
            array("<?php namespace Test; function x(){};", array()),
            array("<?php namespace Test; wrong!;", false),
            array("<?php namespace " . __NAMESPACE__ . ";\n class PluginLoaderTest{}", false),
            array("<?php namespace {class ATest{}}
            namespace Test { class ATestClass{} }", array("\\ATest", "\\Test\\ATestClass")),
        );
    }
}
