<?php

namespace Cybits;

use Cybits\Elbish\Application;
use Cybits\Elbish\Template\Manager;
use Cybits\Elbish\Template\Pager;
use Testing\TestingBootstrap;

/**
 * Class MiscTest
 *
 * @package Cybits
 */
class MiscTest extends \PHPUnit_Framework_TestCase
{
    public function testPager()
    {
        $pager = new Pager(999, ':page', 10);
        $this->assertEquals(':page', $pager->getPattern());
        $pager->setPattern('page/:page');
        $this->assertEquals('page/:page', $pager->getPattern());
        $this->assertEquals('page/100', $pager->getTarget(100));
        $this->assertEquals(7, count($pager->getIterator()));
        $pager->setCurrentPage(100);
        $this->assertEquals(7, count($pager->getIterator()));
        $pager->setCurrentPage(50);
        $this->assertEquals(13, count($pager->getIterator()));
    }

    public function testTemplateManager()
    {
        $app = Application::createInstance(TestingBootstrap::getLoader());
        $template = new Manager($app);

        $this->assertInstanceOf('\\Cybits\\Elbish\\Template\\Engine\\Twig', $template->getEngine('twig'));
        $this->setExpectedException('\\Cybits\\Elbish\\Exception\\NotFound');
        $template->getEngine('not-available');
    }


    protected function setUp()
    {
        TestingBootstrap::getLoader();
    }
}
