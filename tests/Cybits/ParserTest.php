<?php

namespace Cybits;

use Cybits\Elbish\Parser\Post\Markdown;
use Cybits\Elbish\Parser\Post;

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
        $post = new Post();
        $this->assertTrue($post->loadFrontMatter($this->examplePath . "/posts/example.md"));
        $this->assertContains("This is an example post.", $post->getText());
        $this->assertTrue($post->isSupported($this->examplePath . "/posts/example.md"));
        $this->assertTrue($post->isSupported($this->examplePath . "/config.yaml"));

        $post = new Markdown();
        $this->assertTrue($post->loadFrontMatter($this->examplePath . "/posts/example.md"));
        $this->assertContains("<p>This is an example post.</p>", $post->getText());
        $this->assertTrue($post->isSupported($this->examplePath . "/posts/example.md"));
        $this->assertFalse($post->isSupported($this->examplePath . "/config.yaml"));

        $this->assertFalse($post->loadFrontMatter($this->examplePath . "/config.yaml"));
        $this->assertFalse($post->loadFrontMatter($this->examplePath . "/config.md"));
    }

    public function testPostDate()
    {
        $post = new Post();
        $this->assertTrue($post->loadFrontMatter($this->examplePath . "/posts/example.md"));
        $this->assertEquals(strtotime($post['date']), $post->getDate());
        $this->assertTrue($post->loadFrontMatter($this->examplePath . "/posts/example-no-date.md"));
        $this->assertEquals(filectime($this->examplePath . "/posts/example-no-date.md"), $post->getDate());
    }
}
