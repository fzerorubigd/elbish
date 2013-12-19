<?php

namespace Cybits;

use Cybits\Elbish\Plugin\Loader;
use Symfony\Component\Console\Tester\CommandTester;
use Testing\TestingBootstrap;

class PluginLoaderTest extends \PHPUnit_Framework_TestCase
{


    private $examplePath;

    public function setUp()
    {
        TestingBootstrap::getLoader(); // Make sure the autoloader is active
        $this->examplePath = realpath(__DIR__ . "/../example");
    }

    /**
     * @dataProvider codeProvider
     */
    public function testValidLoader($code, $classes)
    {
        static $count = 1;
        $directory = sys_get_temp_dir() . "/test_" . time() . $count++;

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

    public function testIfDirectoryIsNotExists()
    {
        $directory = '/oh/if/you/have/this/kill/yourself';
        $loader = new Loader();

        $this->assertFalse($loader->loadPlugins($directory));
    }


    public function codeProvider()
    {
        return array(
            array("<?php class TestClass{} ", array("\\TestClass")),
            array("<?php namespace Test;use Cybits\\Elbish\\Parser\\Post;
            class TestClass extends Post{} ", array("\\Test\\TestClass")),
            array("<?php namespace Test { class AnotherTestClass{} }", array("\\Test\\AnotherTestClass")),
            array("<?php namespace Test; function x(){};", array()),
            array("<?php namespace Test; wrong!;", false),
            array("<?php namespace " . __NAMESPACE__ . ";\n class PluginLoaderTest{}", false),
            array("<?php namespace {class ATest{}}
            namespace Test { class ATestClass{} }", array("\\ATest", "\\Test\\ATestClass")),
        );
    }

    public function testApplicationPlugins()
    {
        chdir($this->examplePath);

        $app = Elbish\Application::createInstance();

        $this->assertInstanceOf('\Cybits\Elbish\Plugin\Loader', $app->getPluginLoader());
        $this->assertTrue(class_exists('\\ExampleParser\\ExampleParser'));
        $command = $app->find('build-posts');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName(),
                '--force' => true
            )
        );
        $this->assertContains("Processing plugin.example  .... DONE", $cmdTester->getDisplay());
        $this->assertContains(
            strrev('Oh, this should be reverse.'),
            file_get_contents($this->examplePath . '/_target/example/13/12/plugin/index.html')
        );
    }
}
