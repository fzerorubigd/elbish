<?php

namespace Cybits\Elbish\Console\Command;


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
            $this->getApplication()->getConfig()->get('site.cache_dir', '.cache');
        try {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }
            file_put_contents($cacheDir . '/posts.cache.yaml', Yaml::dump($this->cache));
        } catch (\Exception $e) {
            //TODO : change php warnings to error
            // :/ do nothing.
        }
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
     * Process a file
     *
     * @param SplFileInfo $file         file data
     * @param string      $targetFolder target folder to save the result into
     *
     * @return bool
     */
    private function processFile(SplFileInfo $file, $targetFolder)
    {
        $post = $this->getApplication()->getParserForFile($file->getRealPath());
        $post->loadFrontMatter($file->getRealPath());
        $twig = $this->getApplication()->getTwig();
        //TODO : Template plugin to use other type of markups, like mustache or handlebars
        $result = $twig->render('post.twig', array('post' => $post));

        $target = $this->getApplication()->getConfig()->get('site.post_url', ':year/:month/:slug');
        $noExt = $this->getApplication()->getConfig()->get('site.no_ext', true);

        $overwrite = array(':slug' => $file->getBasename('.' . $file->getExtension()));
        if (isset($post['date'])) {
            $date = strtotime($post['date']);
        } else {
            // Try to load it from file time, not a good way, but what can I do??
            $date = $file->getCTime();
        }
        foreach ($post as $key => $value) {
            if (is_scalar($value)) {
                $overwrite[':' . $key] = $value;
            }
        }
        $target = $targetFolder . '/' . $this->getPattern($target, $date, $overwrite);
        if ($noExt) {
            $target .= '/index.html';
        } else {
            $target .= '.html';
        }

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        file_put_contents($target, $result);
        $this->addDataToCache($file->getRealPath(), 'target', $target);
        $this->addDataToCache($file->getRealPath(), 'target_md5', md5_file($target));

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
        if (isset($this->cache[$identifier])) {
            $data = $this->cache[$identifier];
            if (isset($data['md5']) &&
                isset($data['target']) &&
                isset($data['target_md5']) &&
                $data['md5'] == $hash &&
                is_readable($data['target']) &&
                md5_file($data['target']) == $data['target_md5']
            ) {
                return true;
            }
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
