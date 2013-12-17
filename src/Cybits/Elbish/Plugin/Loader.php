<?php

namespace Cybits\Elbish\Plugin;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Loader, A simple class to safe-load plugins
 *
 * @package Cybits\Elbish\Plugin
 */
class Loader
{
    /**
     * Try to load plugins from plugin directory
     *
     * @param string $directory directory to load
     *
     * @return array of filename => array($object, ...)
     */
    public function loadPlugins($directory)
    {
        $result = array();
        $finder = Finder::create();
        $finder->files()->in($directory)->name('/\.php$/');
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            try {
                $data = $parser->parse($file->getContents());

                $classes = $this->findClasses($data);
                foreach ($classes as $class => &$object) {
                    $object = $this->safeLoadClasses($class, $file->getRealPath());
                }
                $result[$file->getRealPath()] = $classes;

            } catch (\Exception $e) {
                $result[$file->getRealPath()] = false;
            }
        }

        return $result;
    }

    /**
     * Find classes inside the $data array from parser, recursive.
     *
     * @param \PHPParser_Node[] $data   array of nodes
     * @param string            $prefix last namespace name
     *
     * @return array of class names
     */
    private function findClasses($data, $prefix = '')
    {
        $classes = array();
        foreach ($data as $node) {
            if ($node->getType() == 'Stmt_Namespace') {
                if ($node->name != '') {
                    $newPrefix = $prefix . '\\' . $node->name;
                } else {
                    $newPrefix = $prefix . $node->name;
                }
                $classes = array_merge($classes, $this->findClasses($node->stmts, $newPrefix));
            } elseif ($node->getType() == 'Stmt_Class') {
                $class = $prefix . '\\' . $node->name;
                if (class_exists($class)) {
                    return false;
                } else {
                    $classes[$prefix . '\\' . $node->name] = true;
                }
            }
        }

        return $classes;
    }

    /**
     * Load a plugin class
     *
     * @param string $className class name to create
     * @param string $fileName  filename to load
     *
     * @return mixed
     */
    private function safeLoadClasses($className, $fileName)
    {
        if (!class_exists($className)) {
            // Maybe require_once and then throw an exception on error
            require($fileName);
        }

        $reflection = new \ReflectionClass($className);

        return $reflection->newInstance();
    }
}
