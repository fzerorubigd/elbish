<?php

namespace Cybits;

use Cybits\Elbish\Exception\NotSupported;
use Cybits\Elbish\Parser\Post\Markdown;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    private $examplePath;

    public function setUp()
    {
        $this->examplePath = realpath(__DIR__ . "/../example");
    }

    public function testEmptyConfig()
    {
        $empty = new Elbish\Parser\Config(false);
        $this->assertEquals('default', $empty->get('not.exist', 'default'));
        $this->assertFalse($empty->has('not.exists'));
        $this->assertFalse($empty->offsetExists('not.exists'));
    }

    public function testValidConfig()
    {
        $config = new Elbish\Parser\Config($this->examplePath . "/config.yaml");
        $this->assertEquals("This is my title", $config['site.title']);
        $this->assertTrue($config->has('site.title'));
        $this->assertTrue($config->offsetExists('site.title'));
    }

    /**
     * @expectedException \Cybits\Elbish\Exception\NotSupported
     */
    public function testSetConfig()
    {
        $empty = new Elbish\Parser\Config(false);
        $empty['test'] = 'test';
    }

    /**
     * @expectedException \Cybits\Elbish\Exception\NotSupported
     */
    public function testUnsetConfig()
    {
        $config = new Elbish\Parser\Config($this->examplePath . "/config.yaml");
        unset($config['site.title']);
    }

    public function testValidPost()
    {
        $post = new Markdown($this->examplePath . "/posts/example.md");
        $this->assertContains("<p>This is an example post.</p>", $post->getText());
        $this->assertTrue($post->isSupported($this->examplePath . "/posts/example.md"));
        $this->assertFalse($post->isSupported($this->examplePath . "/config.yaml"));
    }

    public function testNewPostCommand()
    {
        chdir($this->examplePath . "/posts");
        if (file_exists('__/__example__.md')) {
            @unlink('__/__example__.md');
            @rmdir('__');
        }
        $app = new Elbish\Application();
        $command = $app->find('new-post');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName(),
                'filename' => "__/__example__",
                'title' => "Test post",
                'ext' => 'md'
            )
        );
        $text = file_get_contents('__/__example__.md');
        $this->assertContains("Test post", $text);

        @unlink('__/__example__.md');
        @rmdir('__');
    }

    /**
     * @expectedException \Cybits\Elbish\Exception\GeneralException
     */
    public function testNewPostCommandIfExists()
    {
        chdir($this->examplePath . "/posts");
        if (!file_exists('__example__.md')) {
            touch('__example__.md');
        }
        $app = new Elbish\Application();
        $command = $app->find('new-post');

        $cmdTester = new CommandTester($command);

        try {
            $cmdTester->execute(
                array(
                    'command' => $command->getName(),
                    'filename' => "__example__",
                    'title' => "Test post",
                    'ext' => 'md'
                )
            );
        } catch (\Exception $e) {
            @unlink('__example__.md');
            throw $e;
        }
    }
}