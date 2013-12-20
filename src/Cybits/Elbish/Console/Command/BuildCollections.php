<?php

namespace Cybits\Elbish\Console\Command;


use Cybits\Elbish\Parser\Collection;
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
class BuildCollections extends Base
{
    protected $cache = array();

    /**
     * Try to load cache
     */
    protected function loadCache()
    {
        $cacheDir = $this->getApplication()->getCurrentDir() . '/' .
            $this->getApplication()->getConfig()->get('site.cache_dir', '_cache');
        // Cache must be there. so no exception is expected
        $this->cache = Yaml::parse($cacheDir . '/posts.cache.yaml');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("build-collections")
            ->setDescription("Build collections.")
            ->setDefinition(array())
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force rebuild all posts before building the collection')
            ->setHelp(
                <<<EOT
    Build all collections.
EOT
            );
    }

    /**
     * Get target file for a collection
     *
     * @param \Cybits\Elbish\Parser\Collection $collection collection object
     * @param \SplFileInfo                     $file       post file
     *
     * @return array|string
     */
    private function getTargetFolder(Collection $collection, \SplFileInfo $file)
    {
        //TODO: Collection can change the url pattern
        $target = $this->getApplication()->getConfig()->get('site.collection_url', ':slug/:page');

        $overwrite = array(
            ':slug' => $file->getBasename('.' . $file->getExtension()),
            ':ext' => $file->getExtension(),
            ':page' => ':page'
        );

        foreach ($collection as $key => $value) {
            if (is_scalar($value)) {
                $overwrite[':' . $key] = $value;
            }
        }
        $collection['_url'] = $url = $this->getPattern($target, time(), $overwrite);
        $collection['_target'] = $target = $url . '/index.html';

        return $target;
    }

    /**
     * Execute build-post command
     *
     * @param InputInterface  $input  input data from current command
     * @param OutputInterface $output output stream for current command
     */
    protected function buildPosts(InputInterface $input, OutputInterface $output)
    {
        $buildPosts = $this->getApplication()->find('build-posts');
        $buildPosts->run($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // First try to run the build posts command
        $this->buildPosts($input, $output);
        //Ok then get the collection
        $collectionsFolder = $this->getApplication()->getCurrentDir() .
            '/' .
            $this->getApplication()->getConfig()->get('collections.path', 'collections');
        $targetFolder = $this->getApplication()->getCurrentDir() .
            '/' .
            $this->getApplication()->getConfig()->get('site.target_dir', '_target');
        $finder = Finder::create();
        $finder->files()->in($collectionsFolder);
        $this->loadCache();
        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            $output->write('<info>Processing collection ' . $file->getFilename() . '</info> ');
            $this->processFile($file, $targetFolder);
            $output->writeln(' .... <info>DONE</info>');
        }
    }

    /**
     * Process a collection file
     *
     * @param \SplFileInfo $file         file to process
     * @param string       $targetFolder target folder
     */
    protected function processFile(\SplFileInfo $file, $targetFolder)
    {
        $collection = $this->getApplication()
            ->getParserManager()
            ->getParserForCollectionFile($file->getRealPath());
        $collection->loadYamlFile($file->getRealPath());
        $posts = $this->findPosts($collection);
        $target = $targetFolder . '/' . $this->getTargetFolder($collection, $file);
        $results = $collection->render($posts);

        foreach ($results as $page => $result) {
            $current = strtr($target, array(':page' => $page));
            if (!is_dir(dirname($current))) {
                mkdir(dirname($current), 0777, true);
            }

            file_put_contents($current, $result);
        }
    }

    /**
     * Find posts in collection base on cache
     *
     * @param Collection $collection collection to add posts
     *
     * @return Post[]
     */
    protected function findPosts(Collection $collection)
    {
        $result = array();
        foreach ($this->cache as $data) {
            $post = $this->getApplication()
                ->getParserManager()->getParserForPostFile($data['file']);
            $post->loadFrontMatter($data['file']);
            if ($collection->isIncluded($post)) {
                $post['_url'] = $data['url'];
                $post['_target'] = $data['target'];
                $result[] = $post;
            } else {
                unset($post);
            }
        }

        //Sort result with this collection compare function.
        usort($result, array($collection, 'compare'));

        return $result;
    }
}
