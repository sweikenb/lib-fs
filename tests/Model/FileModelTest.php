<?php declare(strict_types=1);

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Filesystem\Exceptions\FileRenameException;
use Sweikenb\Library\Filesystem\Model\Directory;
use Sweikenb\Library\Filesystem\Model\File;

class FileModelTest extends TestCase
{
    const TMP_FILE = '/tmp/sweikenb-lib-fs-FileModelTest';
    const TMP_FILE_RENAME_REL = 'sweikenb-lib-fs-FileModelTest_rename';
    const TMP_FILE_RENAME_ABS = '/tmp/sweikenb-lib-fs-FileModelTest_rename';

    const TMP_DATA_A = 'data A';
    const TMP_DATA_B = 'data B';

    public function tearDown(): void
    {
        if (file_exists(self::TMP_FILE)) {
            unlink(self::TMP_FILE);
        }
        if (file_exists(self::TMP_FILE_RENAME_ABS)) {
            unlink(self::TMP_FILE_RENAME_ABS);
        }
    }

    public function initDataProvider(): array
    {
        $parent = new Directory('/abs/dir', './dir');
        return [
            ['/abs/path', './rel', null],
            ['/abs/path', './rel', $parent],
            ['/abs/path', './rel', $parent],
        ];
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getAbsPath
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getRelPath
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getParentDir
     * @dataProvider initDataProvider
     */
    public function testInit(string $abs, string $rel, ?Directory $parent): void
    {
        $file = new File('/abs/path', './rel', $parent);
        $this->assertEquals($abs, $file->getAbsPath());
        $this->assertEquals($rel, $file->getRelPath());
        $this->assertSame($parent, $file->getParentDir());
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers \Sweikenb\Library\Filesystem\Model\File::getContent
     */
    public function testContent(): void
    {
        $file = new File(__FILE__, basename(__FILE__));
        $this->assertSame(md5_file(__FILE__), md5($file->getContent()));
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers \Sweikenb\Library\Filesystem\Model\File::persist
     * @covers \Sweikenb\Library\Filesystem\Model\File::setContent
     * @covers \Sweikenb\Library\Filesystem\Model\File::getContent
     * @covers \Sweikenb\Library\Filesystem\Model\File::pendingRenameOnPersist
     */
    public function testPersist(): void
    {
        file_put_contents(self::TMP_FILE, self::TMP_DATA_A);
        chdir(dirname(self::TMP_FILE));

        $this->assertSame(self::TMP_DATA_A, file_get_contents(self::TMP_FILE));

        $file = new File(self::TMP_FILE, basename(self::TMP_FILE));

        $ref = new \ReflectionClass($file);
        $prop = $ref->getProperty('content');
        $prop->setAccessible(true);
        $this->assertNull($prop->getValue($file));

        $this->assertSame(self::TMP_DATA_A, $file->getContent());
        $this->assertSame(self::TMP_DATA_A, $prop->getValue($file));

        $file->setContent(self::TMP_DATA_B);
        $this->assertSame(self::TMP_DATA_B, $file->getContent());
        $this->assertSame(self::TMP_DATA_B, $prop->getValue($file));
        $this->assertTrue($file->persist());
        $this->assertNull($prop->getValue($file));

        $this->assertSame(self::TMP_DATA_B, file_get_contents(self::TMP_FILE));
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers \Sweikenb\Library\Filesystem\Model\File::getAbsPath
     * @covers \Sweikenb\Library\Filesystem\Model\File::getRelPath
     * @covers \Sweikenb\Library\Filesystem\Model\File::renameOnPersist
     * @covers \Sweikenb\Library\Filesystem\Model\File::pendingRenameOnPersist
     * @covers \Sweikenb\Library\Filesystem\Model\File::persist
     */
    public function testRenameOnPersistWithoutContentChange(): void
    {
        file_put_contents(self::TMP_FILE, self::TMP_DATA_A);
        chdir(dirname(self::TMP_FILE));

        $file = new File(self::TMP_FILE, basename(self::TMP_FILE));
        $this->assertFalse($file->pendingRenameOnPersist());

        $file->renameOnPersist(self::TMP_FILE_RENAME_REL);
        $this->assertTrue($file->pendingRenameOnPersist());

        $this->assertFileExists(self::TMP_FILE);
        $this->assertFileDoesNotExist(self::TMP_FILE_RENAME_ABS);

        $this->assertTrue($file->persist());

        $this->assertFalse($file->pendingRenameOnPersist());
        $this->assertFileExists(self::TMP_FILE_RENAME_ABS);
        $this->assertFileDoesNotExist(self::TMP_FILE);
    }

    /**
     * @covers \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers \Sweikenb\Library\Filesystem\Model\File::getAbsPath
     * @covers \Sweikenb\Library\Filesystem\Model\File::getRelPath
     * @covers \Sweikenb\Library\Filesystem\Model\File::renameOnPersist
     * @covers \Sweikenb\Library\Filesystem\Model\File::pendingRenameOnPersist
     * @covers \Sweikenb\Library\Filesystem\Model\File::persist
     * @covers \Sweikenb\Library\Filesystem\Model\File::setContent
     */
    public function testRenameOnPersistWithContentChange(): void
    {
        file_put_contents(self::TMP_FILE, self::TMP_DATA_A);
        chdir(dirname(self::TMP_FILE));
        $content = 'Some file content';

        $file = new File(self::TMP_FILE, basename(self::TMP_FILE));

        $this->assertSame(self::TMP_DATA_A, file_get_contents(self::TMP_FILE));
        $this->assertFalse($file->pendingRenameOnPersist());

        $file->setContent($content);
        $this->assertFalse($file->pendingRenameOnPersist());

        $file->renameOnPersist(self::TMP_FILE_RENAME_REL);
        $this->assertTrue($file->pendingRenameOnPersist());

        $this->assertTrue($file->persist());

        $this->assertFalse($file->pendingRenameOnPersist());
        $this->assertFileExists(self::TMP_FILE_RENAME_ABS);
        $this->assertFileDoesNotExist(self::TMP_FILE);

        $this->assertSame($content, file_get_contents(self::TMP_FILE_RENAME_ABS));
    }

    public function renameFilenameProvider(): array
    {
        return [
            ['', false],
            ['..', false],
            ['../', false],
            ['/', false],
            ['/foo', false],
            ['./', false],
            ['./foo', false],
            ['./foo', false],
            ['.', true],
            ['.foo', true],
            ['foo_bar_baz', true],
        ];
    }

    /**
     * @covers       \Sweikenb\Library\Filesystem\Model\File::__construct
     * @covers       \Sweikenb\Library\Filesystem\Model\File::renameOnPersist
     * @covers       \Sweikenb\Library\Filesystem\Model\File::pendingRenameOnPersist
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getAbsPath
     * @covers       \Sweikenb\Library\Filesystem\Model\File::getRelPath
     *
     * @dataProvider renameFilenameProvider
     */
    public function testRenameOnPersistWithInvalidFilenames(string $filename, bool $allowed): void
    {
        if (!$allowed) {
            $this->expectException(FileRenameException::class);
        }

        $file = new File(self::TMP_FILE, basename(self::TMP_FILE));
        $file->renameOnPersist($filename);
        $this->assertTrue($file->pendingRenameOnPersist());
    }
}
