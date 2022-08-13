<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Api;

use Sweikenb\Library\Filesystem\Exceptions\FileRenameException;

interface FileInterface
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
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     *
     * @return void
     */
    public function setContent(string $content): void;

    /**
     * @return bool
     */
    public function persist(): bool;

    /**
     * @param string $newRelFilename
     *
     * @return void/**
     *
     * @throws FileRenameException
     */
    public function renameOnPersist(string $newRelFilename): void;

    /**
     * @return bool
     */
    public function pendingRenameOnPersist(): bool;
}
