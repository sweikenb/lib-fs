<?php declare(strict_types=1);

namespace Sweikenb\Library\Filesystem\Service;

use Sweikenb\Library\Filesystem\Api\DirectoryInterface;
use Sweikenb\Library\Filesystem\Exceptions\DirectoryTreeException;
use Sweikenb\Library\Filesystem\Factory\DirectoryFactory;
use Sweikenb\Library\Filesystem\Factory\FileFactory;

class DirectoryTreeService
{
    private DirectoryFactory $directoryFactory;
    private FileFactory $fileFactory;

    /**
     * @param DirectoryFactory|null $directoryFactory
     * @param FileFactory|null $fileFactory
     */
    public function __construct(?DirectoryFactory $directoryFactory = null, ?FileFactory $fileFactory = null)
    {
        $this->directoryFactory = $directoryFactory ?? new DirectoryFactory();
        $this->fileFactory = $fileFactory ?? new FileFactory();
    }

    /**
     * @throws DirectoryTreeException
     */
    public function fetchTree(string $dir, bool $skippHidden = true): DirectoryInterface
    {
        $absParentDir = @realpath($dir);
        if (!$absParentDir) {
            throw new DirectoryTreeException(sprintf('Can not find directors: %s (%s)', $dir, $absParentDir));
        }
        return $this->load(null, $absParentDir, '', $skippHidden);
    }

    /**
     * @throws DirectoryTreeException
     */
    private function load(
        ?DirectoryInterface $parentDir,
        string $absParentDir,
        string $relParentPath,
        bool $skippHidden
    ): DirectoryInterface {
        $directory = $this->directoryFactory->create($absParentDir, $relParentPath, $parentDir);
        $files = @scandir($absParentDir);
        if ($files === false) {
            throw new DirectoryTreeException(
                sprintf('Can not load directory content for directors: %s (%s)', $relParentPath, $absParentDir)
            );
        }
        foreach ($files as $file) {
            if (in_array($file, ['.', '..']) || ($skippHidden && $file[0] === '.')) {
                continue;
            }
            $absPath = sprintf('%s/%s', $absParentDir, $file);
            $relPath = trim(sprintf('%s/%s', $relParentPath, $file), '/');
            if (is_dir($absPath)) {
                $child = $this->load($directory, $absPath, $relPath, $skippHidden);
                $directory->addChildDir($child);
            } else {
                $child = $this->fileFactory->create($absPath, $relPath, $directory);
                $directory->addFile($child);
            }
        }
        return $directory;
    }
}
