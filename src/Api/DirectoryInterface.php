<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Api;

interface DirectoryInterface
{
    /**
     * @return string
     */
    public function getAbsPath(): string;

    /**
     * @return string
     */
    public function getRelPath(): string;

    /**
     * @return DirectoryInterface|null
     */
    public function getParentDir(): ?DirectoryInterface;

    /**
     * @param FileInterface[] $files
     */
    public function setFiles(array $files): void;

    /**
     * @param FileInterface $file
     */
    public function addFile(FileInterface $file): void;

    /**
     * @return FileInterface[]
     */
    public function getFiles(): array;

    /**
     * @return DirectoryInterface[]
     */
    public function getChildDirs(): array;

    /**
     * @param DirectoryInterface[] $childDirs
     */
    public function setChildDirs(array $childDirs): void;

    /**
     * @param DirectoryInterface $childDir
     */
    public function addChildDir(DirectoryInterface $childDir): void;

    /**
     * @return bool
     */
    public function persist(): bool;
}
