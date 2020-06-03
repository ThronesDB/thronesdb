<?php
namespace App\Services;

use Symfony\Component\Config\FileLocator;

/**
 * Service class for locating dataimport files.
 *
 * Class DataimportFileLocator
 * @package App\Services
 */
class DataimportFileLocator
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string Default data-files directory path.
     */
    const DATAIMPORT_FILE_DIR = '@IliosCoreBundle/Resources/dataimport';

    /**
     * @var FileLocator
     */
    protected $fileLocator;

    public function __construct($kernelRootDirectory)
    {
        $path = realpath($kernelRootDirectory . '/../config/dataimport');
        $this->fileLocator = new FileLocator();
        $this->setDirectoryPath($path);
    }

    /**
     * Finds and return the absolute path to a given dataimport file.
     *
     * @param string $fileName name of the data file.
     * @return string the absolute path.
     */
    public function getDataFilePath($fileName)
    {
        $path = $this->fileLocator->locate($this->getDirectoryPath() . DIRECTORY_SEPARATOR . basename($fileName));
        return $path;
    }

    /**
     * @param string $dir The relative path to the data-files directory.
     */
    public function setDirectoryPath($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return string The relative path to the data-files directory.
     */
    public function getDirectoryPath()
    {
        return $this->dir;
    }
}
