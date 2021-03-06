<?php

namespace Cybits;

use Cybits\Elbish\Console\Command\BuildCollections;
use Cybits\Elbish\Console\Command\BuildPosts;
use Cybits\Elbish\Console\Command\NewPost;
use Symfony\Component\Console\Tester\CommandTester;
use Testing\TestingBootstrap;

/**
 * Class CommandsTest
 *
 * @package Cybits
 */
class CommandsTest extends \PHPUnit_Framework_TestCase
{

    private $examplePath;

    /**
     * Setup the tests
     */
    public function setUp()
    {
        TestingBootstrap::getLoader(); // Make sure the autoloader is active
        $this->examplePath = realpath(__DIR__ . "/../example");
    }

    public function testNewPostCommand()
    {
        chdir($this->examplePath . "/posts");
        if (file_exists('__/__example__.md')) {
            @unlink('__/__example__.md');
            @rmdir('__');
        }
        $app = Elbish\Application::createInstance(TestingBootstrap::getLoader());
        $app->add(new NewPost());
        $this->assertInstanceOf('\\Cybits\\Elbish\\Parser\\Config', $app->getConfig());
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
        $app = Elbish\Application::createInstance(TestingBootstrap::getLoader());
        $app->add(new NewPost);
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

    /**
     * @param $dir
     *
     * @return bool
     */
    private function delTree($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    public function testBuildPosts()
    {
        chdir($this->examplePath);
        $this->delTree('_cache');
        $this->delTree('_target');

        $app = Elbish\Application::createInstance(TestingBootstrap::getLoader());
        $app->add(new BuildPosts());
        $command = $app->find('build-posts');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains("Processing example.md  .... DONE", $cmdTester->getDisplay());
        $this->assertFileExists($this->examplePath . '/_target/md/13/12/example/index.html');
        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );
        $this->assertContains("Processing example.md  .... File has no change. skipping", $cmdTester->getDisplay());
        $this->assertFileExists($this->examplePath . '/_target/md/13/12/example/index.html');
        $cmdTester->execute(
            array(
                'command' => $command->getName(),
                '--force' => true
            )
        );
        $this->assertContains("Processing example.md  .... DONE", $cmdTester->getDisplay());
        //Make the cache invalid
        file_put_contents('_cache/posts.cache.yaml', "Invalid\n yaml\n\t file");
        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains("Processing example.md  .... DONE", $cmdTester->getDisplay());
        $this->assertFileExists($this->examplePath . '/_target/md/13/12/example/index.html');
    }


    public function testBuildCollections()
    {
        chdir($this->examplePath);
        $this->delTree('_cache');
        $this->delTree('_target');

        $app = Elbish\Application::createInstance(TestingBootstrap::getLoader());
        $app->add(new BuildPosts());
        $app->add(new BuildCollections());
        $command = $app->find('build-collections');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertContains("Processing collection all.yaml  .... DONE", $cmdTester->getDisplay());
    }

    public function testIndexPage()
    {
        chdir($this->examplePath);
        $this->delTree('_cache');
        $this->delTree('_target');

        $app = Elbish\Application::createInstance(TestingBootstrap::getLoader());
        $app->getConfig()->set('site.index', 'posts/example.md');
        $app->add(new BuildPosts());
        $app->add(new BuildCollections());
        $command = $app->find('build-collections');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertFileEquals(
            $this->examplePath . '/_target/index.html',
            $this->examplePath . '/_target/md/13/12/example/index.html'
        );

        $app->getConfig()->set('site.index', 'collections/all.yaml');
        $command = $app->find('build-collections');

        $cmdTester = new CommandTester($command);

        $cmdTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertFileEquals(
            $this->examplePath . '/_target/index.html',
            $this->examplePath . '/_target/tag/posts/1/index.html'
        );
    }
}
