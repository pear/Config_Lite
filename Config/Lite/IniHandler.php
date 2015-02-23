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
class Config_Lite_IniHandler extends Config_Lite_BaseHandler
    implements Config_Lite_HandlerInterface
{
    /**
     * global section array key
     *
     * @var string
     */
    const GLOBAL_SECT = '__global__';

    /**
     * delimiter Regular expressions
     *
     * @var string
     */
    const RE_DELIM = '/';

    /**
     * Regular expressions for parsing section headers and options.
     */
    const SECT_RE = '^\[(?P<header>[^]]+)\]';


    const OPT_RE = '(?P<option>[^:=\s][^:=]*)\s*(?P<vi>[:=])\s*(?P<value>.*)$';


    /**
     * parse line test if it is an option
     *
     * @param string $filename Filename
     * @param bool   $processSections process sections
     *
     * @return mixed - array sections or bool false on failure
     * @throws Config\Lite\Exception\Runtime when file not found
     * @throws Config\Lite\Exception\Runtime when file is not readable
     * @throws Config\Lite\Exception\Runtime when parse ini file failed
     */
    protected function parseOptionLine($line, $sectname)
    {
        // an option line?
        $re = self::RE_DELIM . self::OPT_RE . self::RE_DELIM;
        preg_match($re, $line, $mo);
        if ($mo) {
            $optname = $mo['option'];
            $vi = $mo['vi'];
            $optval = $mo['value'];
            if ($vi == '=' || $vi == ':') {
                // 'comments ?  ;' is a comment delimiter only if it follows
                /*
                $pos = strpos($optval, ';');
                if ($pos !== false) {
                    if ($pos != -1 && (trim($optval[$pos-1]))) {
                        $optval = substr($optval, $pos);
                    }
                }
                */
            }
            $optval = trim($optval);
            // allow empty values
            if ($optval == '""'
                || $optval == "''"
            ) {
                $optval = '';
            }
            // stringval?
            if (isset($optval[0])) {
                if (($optval[0] == '"'
                        && $optval[strlen($optval) - 1] == '"')
                    ||
                    ($optval[0] == '\''
                        && $optval[strlen($optval) - 1] == '\'')
                ) {
                    $optval = substr($optval, 1, -1);
                } else if (($optval[0] == 'e'
                    && (($optval[1] == '"')
                        ||
                        ($optval[1] == '\'')))
                ) {
                    // env value? e"FOO_BAR" || e'FOO_BAR' // $_ENV
                    $optval = getenv(substr($optval, 2, -1));
                }
            }

            $optname = trim($optname);
            $pos = strpos($optname, '[');
            if (false !== $pos) {
                // get index and pop val into array
                $optarray = substr($optname, 0, $pos);
                $index = substr($optname, $pos + 1, strlen($optname) - $pos);
                $index = trim(str_replace(']', '', $index));
                if (!isset($this->temp[$sectname][$optarray])) {
                    $this->temp[$sectname][$optarray] = 0;
                }
                if ($index == '') {
                    $index = $this->temp[$sectname][$optarray];
                }
                $this->temp[$sectname][$optarray]++;
                if (!isset($this->_sections[$sectname][$optarray])) {
                    $this->_sections[$sectname][$optarray] = array();
                }
                $this->_sections[$sectname][$optarray][$index] = $optval;
                return;
            }

            $this->_sections[$sectname][$optname] = $optval;
        }
    }

    /**
     * the parseIniFile method parses the optional given filename
     * TODO: Comments, ArraySyntax
     *
     * @param string $filename Filename
     * @param bool   $processSections process sections
     *
     * @return mixed - array sections or bool f    alse on failure
     * @throws RuntimeException when file not found
     * @throws RuntimeException when file is not readable
     * @throws RuntimeException when parse ini file failed
     */
    protected function parseIniFile($filename, $processSections = false)
    {
        // if ($processSections) {}
        $file = new \SplFileObject($filename);

        $cursect = '';
        $sectname = self::GLOBAL_SECT;
        $optname = '';
        $lineno = 0;
        while (!$file->eof()) {
            $line = $file->fgets();
            $lineno = $lineno + 1;
            // comment or blank line?
            if ((trim($line) === '')
                || $line[0] === '#'
                || $line[0] === ';'
            ) {
                continue;
            }
            // continuation line?
            if (($line[0] == ' ')
                && ($cursect !== '')
                && ($optname !== '')
                && $value = trim($line)
            ) {
                if ($value) {
                    // $cursect[$optname] .= $value;
                }
            } else { // a section header or option header?
                // is it a section header?
                $re = self::RE_DELIM . self::SECT_RE . self::RE_DELIM;
                preg_match($re, ltrim($line), $mo);
                if ($mo) {
                    $sectname = trim($mo['header']);
                    $cursect = array();
                    $this->_sections[$sectname] = $cursect;
                    // So sections can't start with a continuation line
                    $optname = '';
                    // no section header in the file?
                } else if ($cursect === '') {
                    $this->parseOptionLine($line, $sectname);
                } else {
                    // an option line?
                    $this->parseOptionLine($line, $sectname);
                }
            }
        }
        unset($this->temp);
        return $this->_sections;
    }

    /**
     * the read method parses the optional given filename
     * or already setted filename.
     *
     * this method uses the native PHP function
     * parse_ini_file behind the scenes.
     *
     * @param string $filename Filename
     * @param int    $mode INI_SCANNER_NORMAL | INI_SCANNER_RAW
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
        $sections = $this->parseIniFile($filename, $this->processSections, $mode);
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

