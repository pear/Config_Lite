<?php

require_once __DIR__ . '/HandlerInterface.php';
require_once __DIR__ . '/BaseHandler.php';

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
     * filename
     *
     * @var string
     */
    protected $filename;

    /**
     * line-break chars, default *x: "\n", windows: "\r\n"
     *
     * @var string
     */
    protected $linebreak = "\n";

    /**
     * parseSections - if true, sections will be processed
     *
     * @var bool
     */
    protected $processSections = true;

    /**
     * quote Strings - if true,
     * writes ini files with doublequoted strings
     *
     * @var bool
     */
    protected $quoteStrings = true;



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
     * Generated the output of the ini file, suitable for echo'ing or
     * writing back to the ini file.
     *
     * @param array $sectionsarray array of ini data
     *
     * @return  string
     */
    public function buildOutputString($sectionsarray)
    {
        $content = '';
        $sections = '';
        $globals  = '';
        if (!empty($sectionsarray)) {
            // 2 loops to write `globals' on top, alternative: buffer
            foreach ($sectionsarray as $section => $item) {
                if (!is_array($item)) {
                    $value    = $this->normalizeValue($item);
                    $globals .= $section . ' = ' . $value . $this->linebreak;
                }
            }
            $content .= $globals;
            foreach ($sectionsarray as $section => $item) {
                if (is_array($item)) {
                    $sections .= $this->linebreak
                        . "[" . $section . "]" . $this->linebreak;
                    foreach ($item as $key => $value) {
                        if (is_array($value)) {
                            foreach ($value as $arrkey => $arrvalue) {
                                $arrvalue  = $this->normalizeValue($arrvalue);
                                $arrkey    = $key . '[' . $arrkey . ']';
                                $sections .= $arrkey . ' = ' . $arrvalue
                                    . $this->linebreak;
                            }
                        } else {
                            $value     = $this->normalizeValue($value);
                            $sections .= $key . ' = ' . $value . $this->linebreak;
                        }
                    }
                }
            }
            $content .= $sections;
        }
        return $content;
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
