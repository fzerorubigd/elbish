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
        if (!is_dir($directory)) {
            return false;
        }
        $result = array();
        $finder = Finder::create();
        $finder->files()->in($directory)->name('/\.php$/');
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);

        /** @var $file SplFileInfo */
        foreach ($finder as $file) {
            try {
                $data = $parser->parse($file->getContents());

                $classes = $this->findClasses($file->getRealPath(), $data);
                $result = array_merge($result, $classes);
            } catch (\Exception $e) {
                //Do nothing
            }
        }

        return $result;
    }

    /**
     * Find classes inside the $data array from parser, recursive.
     *
     * @param string            $fileName file name contain this
     * @param \PHPParser_Node[] $data     array of nodes
     * @param string            $prefix   last namespace name
     *
     * @return array of class names
     */
    private function findClasses($fileName, $data, $prefix = '')
    {
        //The return value is valid for no namespace use, but here we are
        //in a namespace so beware of that
        $classes = array();
        foreach ($data as $node) {
            if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
                $newPrefix = $node->name;
                $classes = array_merge($classes, $this->findClasses($fileName, $node->stmts, $newPrefix));
            } elseif ($node instanceof \PHPParser_Node_Stmt_Class) {
                $class = $this->getClassName($fileName, $prefix, $node->name);
                if ($class) {
                    $classes[$class] = $fileName;
                }
            }
        }

        return $classes;
    }

    /**
     * Get class name
     *
     * @param string $fileName filename to check
     * @param string $prefix   prefix of class
     * @param string $name     class name
     *
     * @return false|string class name or false on failure
     */
    private function getClassName($fileName, $prefix, $name)
    {
        if ($prefix) {
            $class = $prefix . '\\' . $name;
        } else {
            $class = $name;
        }
        if (class_exists("\\" . $class)) {
            $reflection = new \ReflectionClass("\\" . $class);
            if ($reflection->getFileName() != $fileName) {
                return false;
            }
        }

        return $class;
    }
}
