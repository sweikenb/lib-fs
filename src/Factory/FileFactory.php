<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Factory;

use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Api\FileInterface;
use Sweikenb\Library\Filesystem\Model\File;

class FileFactory
{
    /**
     * @param string $absPath
     * @param string $relPath
     * @param DirectoryInterface|null $parentDir
     *
     * @return FileInterface
     */
    public function create(string $absPath, string $relPath, ?DirectoryInterface $parentDir = null): FileInterface
    {
        return new File($absPath, $relPath, $parentDir);
    }
}
