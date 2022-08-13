<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Factory;

use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Api\FileInterface;
use Sweikenb\Library\Filesystem\Model\Directory;

class DirectoryFactory
{
    /**
     * @param string $absPath
     * @param string $relPath
     * @param DirectoryInterface|null $parentDir
     * @param FileInterface[] $files
     * @param DirectoryInterface[] $childDirs
     *
     * @return DirectoryInterface
     */
    public function create(
        string $absPath,
        string $relPath,
        ?DirectoryInterface $parentDir = null,
        array $files = [],
        array $childDirs = []
    ): DirectoryInterface {
        return new Directory($absPath, $relPath, $parentDir, $files, $childDirs);
    }
}
