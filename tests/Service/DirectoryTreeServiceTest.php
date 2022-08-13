<?php declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Exceptions\DirectoryTreeException;
use Sweikenb\Library\Filesystem\Service\DirectoryTreeService;

class DirectoryTreeServiceTest extends TestCase
{
    const TMP_TEST_BASEDIR = '/tmp/sweikenb_lib-fs';

    /**
     * @covers \Sweikenb\Library\Filesystem\Service\DirectoryTreeService
     * @covers \Sweikenb\Library\Filesystem\Model\Directory
     * @covers \Sweikenb\Library\Filesystem\Model\File
     * @covers \Sweikenb\Library\Filesystem\Factory\DirectoryFactory
     * @covers \Sweikenb\Library\Filesystem\Factory\FileFactory
     */
    public function testFetchTreeFindFile(): void
    {
        $service = new DirectoryTreeService();
        $tree = $service->fetchTree(__DIR__);

        $found = false;
        foreach ($tree->getFiles() as $file) {
            if ($file->getAbsPath() === __FILE__) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Failed to find required file.');
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Service\DirectoryTreeService
     * @covers \Sweikenb\Library\Filesystem\Model\Directory
     * @covers \Sweikenb\Library\Filesystem\Model\File
     * @covers \Sweikenb\Library\Filesystem\Factory\DirectoryFactory
     * @covers \Sweikenb\Library\Filesystem\Factory\FileFactory
     */
    public function testFetchTreeFindDir(): void
    {
        $service = new DirectoryTreeService();
        $tree = $service->fetchTree(dirname(__DIR__));

        $found = false;
        foreach ($tree->getChildDirs() as $dir) {
            if ($dir->getAbsPath() === __DIR__) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Failed to find required dir.');
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Service\DirectoryTreeService
     * @covers \Sweikenb\Library\Filesystem\Model\Directory
     * @covers \Sweikenb\Library\Filesystem\Model\File
     * @covers \Sweikenb\Library\Filesystem\Factory\DirectoryFactory
     * @covers \Sweikenb\Library\Filesystem\Factory\FileFactory
     */
    public function testFetchTreeError(): void
    {
        $service = new DirectoryTreeService();
        $this->expectException(DirectoryTreeException::class);
        $service->fetchTree(sprintf("%s-not-present", __DIR__));
    }
}
