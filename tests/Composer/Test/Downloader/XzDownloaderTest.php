<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Test\Downloader;

use Composer\Downloader\XzDownloader;

class XzDownloaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('Skip test on Windows');
        }
    }

    public function testErrorMessages()
    {
        $packageMock = $this->getMock('Composer\Package\PackageInterface');
        $packageMock->expects($this->any())
            ->method('getDistUrl')
            ->will($this->returnValue($distUrl = 'file://'.__FILE__))
        ;
        $packageMock->expects($this->any())
            ->method('getDistUrls')
            ->will($this->returnValue(array($distUrl)))
        ;
        $packageMock->expects($this->atLeastOnce())
            ->method('getTransportOptions')
            ->will($this->returnValue(array()))
        ;

        $io = $this->getMock('Composer\IO\IOInterface');
        $io->expects($this->any())->method('getWorkTracker')->willReturn($this->getMock('Composer\IO\WorkTracker\WorkTrackerInterface'));

        $config = $this->getMock('Composer\Config');
        $config->expects($this->any())
            ->method('get')
            ->with('vendor-dir')
            ->will($this->returnValue(sys_get_temp_dir().'/composer-xz-test-vendor'));
        $downloader = new XzDownloader($io, $config);

        try {
            $downloader->download($packageMock, sys_get_temp_dir().'/composer-xz-test');
            $this->fail('Download of invalid tarball should throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertFalse(strpos($e->getMessage(), 'File format not recognized') === false && strpos($e->getMessage(), 'Unrecognized archive format') === false, 'Error message contains `File format not recognized` or `Unrecognized archive format`');
        }
    }
}
