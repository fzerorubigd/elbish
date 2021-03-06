<?php

namespace Cybits\Elbish\Console\Command;


use Cybits\Elbish\Parser\Post;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BuildPosts
 *
 * @package Cybits\Elbish\Console\Command
 */
class BuildPosts extends Base
{
    protected $cache = array();

    /**
     * Try to load cache
     */
    protected function loadCache()
    {
        $cacheDir = $this->getApplication()->getCurrentDir() . '/' .
            $this->getApplication()->getConfig()->get('site.cache_dir', '_cache');

        if (is_readable($cacheDir . '/posts.cache.yaml')) {
            try {
                $this->cache = Yaml::parse($cacheDir . '/posts.cache.yaml');
            } catch (\Exception $e) {
                $this->cache = array();
            }
        }
    }

    /**
     * Try to save cache
     */
    protected function saveCache()
    {
        $cacheDir = $this->getApplication()->getCurrentDir() . '/' .
            $this->getApplication()->getConfig()->get('site.cache_dir', '_cache');
        //try {
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }
        file_put_contents($cacheDir . '/posts.cache.yaml', Yaml::dump($this->cache));
        //} catch (\Exception $e) {
        //TODO : change php warnings to error
        // :/ do nothing.
        //}
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("build-posts")
            ->setDescription("Collect and build posts.")
            ->setDefinition(array())
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force rebuild all, use when template is changed')
            ->setHelp(
                <<<EOT
Build all new or changed posts, can force to build all again
EOT
            );
    }

    /**
     * Get target file for a post
     *
     * @param Post         $post post object
     * @param \SplFileInfo $file post file
     *
     * @return array|string
     */
    private function getTargetFolder(Post $post, \SplFileInfo $file)
    {
        $target = $this->getApplication()->getConfig()->get('site.post_url', ':year/:month/:slug');

        $overwrite = array(
            ':slug' => $file->getBasename('.' . $file->getExtension()),
            ':ext' => $file->getExtension()
        );

        foreach ($post as $key => $value) {
            if (is_scalar($value)) {
                $overwrite[':' . $key] = $value;
            }
        }
        $url = $this->getPattern($target, $post->getDate(), $overwrite) . '/';
        if ($url{0} != '/') {
            $url = '/' . $url;
        }
        $post['_url'] = $url;
        $target = $post['_target'] = $url . 'index.html';
        $this->addDataToCache($file->getRealPath(), 'url', $url);

        return $target;
    }

    /**
     * Process a file
     *
     * @param SplFileInfo $file         file data
     * @param string      $targetFolder target folder to save the result into
     *
     * @return bool
     */
    private function processFile(SplFileInfo $file, $targetFolder)
    {
        $post = $this->getApplication()
            ->getParserManager()->getParserForPostFile($file->getRealPath());
        $post->loadFrontMatter($file->getRealPath());

        $target = $targetFolder . '/' . $this->getTargetFolder($post, $file);
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        $result = $post->render();
        file_put_contents($target, $result);
        if ($post->isIndex()) {
            $this->makeIndexLink($target, $targetFolder . '/index.html');
        }
        $this->addDataToCache($file->getRealPath(), 'target', $target);
        $this->addDataToCache($file->getRealPath(), 'target_md5', md5_file($target));
        unset($post);

        return true;
    }

    /**
     * Add data to cache
     *
     * @param string $fileName file name
     * @param string $data     sting data key
     * @param string $value    value of key
     */
    protected function addDataToCache($fileName, $data, $value)
    {
        $identifier = md5($fileName);
        if (!isset($this->cache[$identifier])) {
            $this->cache[$identifier] = array();
        }
        $this->cache[$identifier]['file'] = $fileName;
        $this->cache[$identifier][$data] = $value;
    }

    /**
     * Is this file has a valid cache or not?
     *
     * @param SplFileInfo $file file info
     *
     * @return bool
     */
    private function isCached(SplFileInfo $file)
    {
        // For now all files are markdown files
        $hash = md5_file($file->getRealPath());
        $identifier = md5($file->getRealPath()); // :)
        if (isset($this->cache[$identifier]) &&
            isset($this->cache[$identifier]['md5']) &&
            isset($this->cache[$identifier]['target']) &&
            isset($this->cache[$identifier]['target_md5']) &&
            $this->cache[$identifier]['md5'] == $hash &&
            is_readable($this->cache[$identifier]['target']) &&
            md5_file($this->cache[$identifier]['target']) == $this->cache[$identifier]['target_md5']
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $postsFolder = $this->getApplication()->getCurrentDir() .
            '/' .
            $this->getApplication()->getConfig()->get('posts.path', 'posts');
        $targetFolder = $this->getApplication()->getCurrentDir() .
            '/' .
            $this->getApplication()->getConfig()->get('site.target_dir', '_target');
        $finder = Finder::create();
        $finder->files()->in($postsFolder);
        $this->loadCache();
        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $output->write('<info>Processing ' . $file->getFilename() . '</info> ');
            if (!$force && $this->isCached($file)) {
                $output->writeln(' .... <info>File has no change. skipping</info>');
            } else {
                $this->processFile($file, $targetFolder);
                $this->addDataToCache($file->getRealPath(), 'md5', md5_file($file->getRealPath()));
                $output->writeln(' .... <info>DONE</info>');
            }
        }
        $this->saveCache();
    }
}
