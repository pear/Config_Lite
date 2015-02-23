<?php

require_once 'Config/Lite/HandlerInterface.php';
require_once 'Config/Lite/BaseHandler.php';

/**
 * Config_Lite_Ini
 *
 * read and save ini text files.
 * Config_Lite_Ini has the native PHP function
 * `parse_ini_file' under the hood.
 * A "Config_Lite" file consists of
 * "name = value" entries and sections,
 * "[section]"
 * followed by "name = value" entries
 *
 * @category  Configuration
 * @package   Config_Lite
 * @author    Patrick C. Engel <pce@php.net>
 * @copyright 2010-2015 <pce@php.net>
 * @license   http://php.net/license/  PHP License
 * @link      https://github.com/pear/Config_Lite
 */
class Config_Lite_NativeIniHandler extends Config_Lite_BaseHandler
    implements Config_Lite_HandlerInterface
{


    /**
     * the read method parses the optional given filename
     * or already setted filename.
     *
     * this method uses the native PHP function
     * parse_ini_file behind the scenes.
     *
     * @param string $filename Filename
     * @param int    $mode     INI_SCANNER_NORMAL | INI_SCANNER_RAW
     *
     * @return Config_Lite
     * @throws Config_Lite_Exception_Runtime when file not found
     * @throws Config_Lite_Exception_Runtime when file is not readable
     * @throws Config_Lite_Exception_Runtime when parse ini file failed
     */
    public function read($filename = null, $mode = 0)
    {
        if (null === $filename) {
            $filename = $this->filename;
        } else {
            $this->filename = $filename;
        }
        if (!file_exists($filename)) {
            throw new Config_Lite_Exception_Runtime('file not found: ' . $filename);
        }
        if (!is_readable($filename)) {
            throw new Config_Lite_Exception_Runtime(
                'file not readable: ' . $filename
            );
        }
        $sections = parse_ini_file($filename, $this->processSections, $mode);
        if (false === $sections) {
            throw new Config_Lite_Exception_Runtime(
                'failure, can not parse the file: ' . $filename
            );
        }
        return $sections;
    }



    /**
     * filename to read or save
     *
     * the full filename with suffix, ie. `[PATH/]<ApplicationName>.ini'.
     * you can also set the filename as parameter to the constructor.
     *
     * @param string $filename Filename
     *
     * @return Config_Lite
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * returns the current filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

}
