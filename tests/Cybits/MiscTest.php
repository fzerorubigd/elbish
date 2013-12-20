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
        $pager = new Pager(100, 10);
        $pager->setPattern(':page');
        $this->assertEquals(':page', $pager->getPattern());
        $this->assertEquals('100', $pager->getTarget(100));
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
