<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Model;

use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Api\FileInterface;

class Directory implements DirectoryInterface
{
    /**
     * @param string $absPath
     * @param string $relPath
     * @param DirectoryInterface|null $parentDir
     * @param FileInterface[] $files
     * @param DirectoryInterface[] $childDirs
     */
    public function __construct(
        private readonly string $absPath,
        private readonly string $relPath,
        private readonly ?DirectoryInterface $parentDir = null,
        private array $files = [],
        private array $childDirs = []
    ) {
    }

    public function getAbsPath(): string
    {
        return $this->absPath;
    }

    public function getRelPath(): string
    {
        return $this->relPath;
    }

    public function getParentDir(): ?DirectoryInterface
    {
        return $this->parentDir;
    }

    /**
     * @param FileInterface[] $files
     */
    public function setFiles(array $files): void
    {
        $this->files = [];
        array_map([$this, 'addFile'], $files);
    }

    public function addFile(FileInterface $file): void
    {
        $this->files[$file->getAbsPath()] = $file;
    }

    /**
     * @return FileInterface[]
     */
    public function getFiles(): array
    {
        return array_values($this->files);
    }

    /**
     * @return DirectoryInterface[]
     */
    public function getChildDirs(): array
    {
        return array_values($this->childDirs);
    }

    /**
     * @param DirectoryInterface[] $childDirs
     */
    public function setChildDirs(array $childDirs): void
    {
        $this->childDirs = [];
        array_map([$this, 'addChildDir'], $childDirs);
    }

    public function addChildDir(DirectoryInterface $childDir): void
    {
        $this->childDirs[$childDir->getAbsPath()] = $childDir;
    }

    public function persist(): bool
    {
        $status = true;
        foreach ($this->files as $file) {
            $status = $file->persist() && $status;
        }
        foreach ($this->childDirs as $dir) {
            $status = $dir->persist() && $status;
        }
        return $status;
    }
}
