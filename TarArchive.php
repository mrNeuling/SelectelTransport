<?php

namespace SelectelTransport;

/**
 * Class TarArchive
 * @package SelectelTransport
 */
class TarArchive
{
    /**
     * @var null|\PharData
     */
    private $archive = null;

    /**
     * TarArchive constructor.
     * @param $archiveName
     * @param int $flags
     * @param string $alias
     */
    public function __construct($archiveName, $flags = null, $alias = null)
    {
        $this->archive = new \PharData($archiveName, $flags, $alias);
    }

    /**
     * Создает tar-архив из директории.
     * Сама корневая директория в архив не попадает
     * @param string $dirName
     */
    public function makeFromDirectory($dirName)
    {
        $dir = opendir($dirName);
        while (($item = readdir($dir)) !== false) {
            if (self::isExcludedDir($item)) continue;

            $subItem = $dirName . '/' . $item;
            if (is_dir($subItem)) {
                $currentDir = getcwd();
                chdir($dirName);

                $this->addSubDirectory($item);

                chdir($currentDir);
            } else {
                $this->archive->addFile($subItem, $item);
            }
        }
    }

    /**
     * Добавляет поддиректорию в архив
     * @param string $dirName
     */
    protected function addSubDirectory($dirName)
    {
        $this->archive->addEmptyDir($dirName);
        $dir = opendir($dirName);
        while (($item = readdir($dir)) !== false) {
            if (self::isExcludedDir($item)) continue;

            $subItem = $dirName . '/' . $item;
            if (is_dir($subItem)) {
                $this->addSubDirectory($subItem);
            } else {
                $this->archive->addFile($subItem);
            }
        }
    }

    /**
     * Провеяет, является ли переданная директория ссылкой на текущую или корневую директорию
     * @param string $dirName
     * @return bool
     */
    protected static function isExcludedDir($dirName)
    {
        return $dirName == '.' || $dirName == '..';
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed|null
     */
    public function __call($method, $arguments)
    {
        if (method_exists($this->archive, $method)) {
            return call_user_func_array([$this->archive, $method], $arguments);
        }

        return null;
    }
}