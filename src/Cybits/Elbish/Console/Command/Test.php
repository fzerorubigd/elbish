<?php
namespace Cybits\Elbish\Console\Command;

use Cybits\Elbish\Parser\Config;
use Cybits\Elbish\Parser\Post;
use Cybits\Yaml\FrontMatter;
use RomaricDrigon\MetaYaml\MetaYaml;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Test extends Command
{
    protected function configure()
    {
        $this->setName("test")
            ->setDescription("Sample description for our command named test")
            ->setDefinition(array())
            ->setHelp(<<<EOT
The <info>test</info> command does things and stuff
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $post = new Post("/home/f0rud/funspace/elbish/example/posts/Post1.yaml");
        $output->writeln("<info>{$post['text']}</info>");
        $output->writeln("<info>" . print_r($post['yaml'], true) . "</info>");
        $config = new Config("/home/f0rud/funspace/elbish/example/config.yaml");
        $output->writeln("<info>{$config['site.title']}</info>");
    }
} 