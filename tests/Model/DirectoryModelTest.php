<?php declare(strict_types=1);

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Api\FileInterface;
use Sweikenb\Library\Filesystem\Model\Directory;
use Sweikenb\Library\Filesystem\Model\File;

class DirectoryModelTest extends TestCase
{
    public function initDataProvider(): array
    {
        $parent = new Directory('', '');
        $file = new File('', '');
        return [
            ['/abs/path', './rel', null, [], []],
            ['/abs/path', './rel', $parent, [], []],
            ['/abs/path', './rel', $parent, [$file], [$parent]],
        ];
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\Directory
     * @dataProvider initDataProvider
     */
    public function testInit(string $abs, string $rel, ?Directory $parent, array $files, array $childDirs): void
    {
        $dir = new Directory($abs, $rel, $parent, $files, $childDirs);
        $this->assertEquals($abs, $dir->getAbsPath());
        $this->assertEquals($rel, $dir->getRelPath());
        $this->assertSame($parent, $dir->getParentDir());
        $this->assertEquals($files, $dir->getFiles());
        $this->assertEquals($childDirs, $dir->getChildDirs());
    }

    public function setAddDataProvider(): array
    {
        $file0 = new File('/abs/file0', './file0');
        $dir0 = new Directory('/abs/dir0', './dir0');
        return [
            [[], []],
            [[$file0], [$dir0]],
        ];
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\Directory
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getAbsPath
     * @dataProvider setAddDataProvider
     */
    public function testSetter(array $files, array $dirs): void
    {
        $dir = new Directory('/abs/path', './rel', null, $files, $dirs);

        $this->assertEquals($files, $dir->getFiles());
        $this->assertEquals($dirs, $dir->getChildDirs());

        $dir->setFiles([]);
        $dir->setChildDirs([]);
        $this->assertEquals([], $dir->getFiles());
        $this->assertEquals([], $dir->getChildDirs());

        $dir->setFiles($files);
        $dir->setChildDirs($dirs);

        $this->assertEquals($files, $dir->getFiles());
        $this->assertEquals($dirs, $dir->getChildDirs());
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\Directory
     * @covers       \Sweikenb\Library\Filesystem\Model\File
     * @dataProvider setAddDataProvider
     */
    public function testAdder(array $files, array $dirs): void
    {
        $dir = new Directory('/abs/path', './rel', null, $files, $dirs);

        $file1 = new File('/abs/file1', './file1');
        $file2 = new File('/abs/file2', './file2');
        $subDir1 = new Directory('/abs/dir1', './dir1');
        $subDir2 = new Directory('/abs/dir2', './dir2');

        // add files & dirs
        $dir->addFile($file1);
        $dir->addFile($file2);
        $dir->addChildDir($subDir1);
        $dir->addChildDir($subDir2);

        // test duplicate add
        $dir->addFile($file1);
        $dir->addChildDir($subDir1);

        $files[] = $file1;
        $files[] = $file2;

        $dirs[] = $subDir1;
        $dirs[] = $subDir2;

        $this->assertEquals($files, $dir->getFiles());
        $this->assertEquals($dirs, $dir->getChildDirs());
    }

    public function persistDataProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\Directory
     * @covers       \Sweikenb\Library\Filesystem\Model\File::persist
     * @dataProvider persistDataProvider
     */
    public function testPersist(bool $dirSuccess, bool $fileSuccess): void
    {
        $dirMock = $this->getMockBuilder(DirectoryInterface::class)->getMock();
        $dirMock
            ->expects($this->once())
            ->method('persist')
            ->willReturn($dirSuccess);

        $fileMock = $this->getMockBuilder(FileInterface::class)->getMock();
        $fileMock
            ->expects($this->once())
            ->method('persist')
            ->willReturn($fileSuccess);

        $dir = new Directory('/abs/path', './rel', null, [$fileMock], [$dirMock]);
        $this->assertSame($dirSuccess && $fileSuccess, $dir->persist());
    }
}
