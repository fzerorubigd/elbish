<?php

namespace Cybits\Elbish\Console\Command;


use Cybits\Elbish\Application;
use Cybits\Elbish\Exception\GeneralException;
use Cybits\Yaml\FrontMatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NewPost
 *
 * @package Cybits\Elbish\Console\Command
 */
class NewPost extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("new-post")
            ->setDescription("Create a new post file.")
            ->setDefinition(array())
            ->addArgument('filename', InputArgument::REQUIRED, 'Target file to create')
            ->addArgument('ext', InputArgument::REQUIRED, 'extension of file to create')
            ->addArgument('title', InputArgument::OPTIONAL, 'Title of post')
            ->setHelp(
                <<<EOT
Create new post file with ext and title
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $app Application */
        $app = $this->getApplication();
        $title = $input->getArgument('title', 'Title');
        $fileName = $input->getArgument('filename');
        $ext = $input->getArgument('ext');
        $yaml = array(
            'title' => $title,
            'date' => date('Y/m/d')
        );
        $text = 'This is my post..';
        $result = FrontMatter::dump($yaml, $text);
        $fileName = $app->getCurrentDir() .
            '/' . $fileName . '.' . $ext;
        if (file_exists($fileName)) {
            throw new GeneralException("File is already available : $fileName");
        }

        $dir = dirname($fileName);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fileName, $result);
        $output->writeln("Create a file name: <info>$fileName</info>");
        $output->writeln("<info>Edit that file in your favorite editor.</info>");
    }
}
