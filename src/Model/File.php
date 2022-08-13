<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Model;

use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Api\FileInterface;
use Sweikenb\Library\Filesystem\Exceptions\FileRenameException;

class File implements FileInterface
{
    private ?string $renameOnPersistAbsPath = null;
    private ?string $renameOnPersistRelPath = null;
    private ?string $content = null;

    public function __construct(
        private string $absPath,
        private string $relPath,
        private readonly ?DirectoryInterface $parentDir = null
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

    public function getContent(): string
    {
        if ($this->content === null) {
            $this->content = (string)file_get_contents($this->absPath);
        }
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function persist(): bool
    {
        $deletePath = null;
        if ($this->pendingRenameOnPersist()) {
            $deletePath = $this->absPath;
            $this->absPath = (string)$this->renameOnPersistAbsPath;
            $this->relPath = (string)$this->renameOnPersistRelPath;
            $this->renameOnPersistAbsPath = null;
            $this->renameOnPersistRelPath = null;
        }
        if ($this->content !== null) {
            if ($deletePath && file_exists($deletePath) && !is_dir($deletePath)) {
                unlink($deletePath);
            }
            if (@file_put_contents($this->absPath, $this->content) === false) {
                return false;
            }
            $this->content = null;
        } else {
            if ($deletePath !== null) {
                rename((string)$deletePath, (string)$this->absPath);
            }
        }
        return true;
    }

    /**
     * @throws FileRenameException
     */
    public function renameOnPersist(string $newRelFilename): void
    {
        if (empty($newRelFilename)
            || mb_substr($newRelFilename, 0, 2) === '..'
            || mb_strpos($newRelFilename, '/') !== false
        ) {
            throw new FileRenameException();
        }
        $this->renameOnPersistAbsPath = sprintf(
            "%s/%s",
            dirname($this->getAbsPath()),
            $newRelFilename
        );
        $this->renameOnPersistRelPath = sprintf(
            "%s/%s",
            dirname($this->getRelPath()),
            $newRelFilename
        );
    }

    public function pendingRenameOnPersist(): bool
    {
        return ($this->renameOnPersistAbsPath !== null && $this->renameOnPersistRelPath !== null);
    }
}
