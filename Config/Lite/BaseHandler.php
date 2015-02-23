<?php

class Config_Lite_BaseHandler
{


    /**
     * string delimiter
     *
     * @var string
     */
    protected $delim = '"';

    /**
     * parseSections - if true, sections will be processed
     *
     * @var bool
     */
    protected $processSections = true;


    /**
     * filename to read
     *
     * @var string
     */
    protected $filename;


    /**
     * quote Strings - if true,
     * writes ini files with doublequoted strings
     *
     * @var bool
     */
    protected $quoteStrings = true;


    /**
     * line-break chars, default *x: "\n", windows: "\r\n"
     *
     * @var string
     */
    protected $linebreak = "\n";

    /**
     * detect Type "bool" by String Value to keep those "untouched"
     *
     * @param string $value value
     *
     * @return bool
     */
    protected function isBool($value)
    {
        return in_array($value, Config_Lite::$booleans);
    }


    /**
     * converts string to a  representable Config Bool Format
     *
     * @param string $value value
     *
     * @return string
     * @throws Config_Lite_Exception_UnexpectedValue when format is unknown
     */
    public function toBool($value)
    {
        if ($value === true) {
            return 'yes';
        }
        return 'no';
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
        $globals = '';
        if (!empty($sectionsarray)) {
            // 2 loops to write `globals' on top, alternative: buffer
            foreach ($sectionsarray as $section => $item) {
                if (!is_array($item)) {
                    $value = $this->normalizeValue($item);
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
                                $arrvalue = $this->normalizeValue($arrvalue);
                                $arrkey = $key . '[' . $arrkey . ']';
                                $sections .= $arrkey . ' = ' . $arrvalue
                                    . $this->linebreak;
                            }
                        } else {
                            $value = $this->normalizeValue($value);
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
     * normalize a Value by determining the Type
     *
     * @param string $value value
     *
     * @return string
     */
    protected function normalizeValue($value)
    {
        if (is_bool($value)) {
            $value = $this->toBool($value);
            return $value;
        } elseif (is_numeric($value)) {
            return $value;
        }
        if ($this->quoteStrings) {
            $value = $this->delim . $value . $this->delim;
        }
        return $value;
    }

    /**
     * set string delimiter to single tick (')
     *
     * @return Config_Lite
     */
    public function setSingleTickDelimiter()
    {
        $this->delim = "'";
        return $this;
    }

    /**
     * set string delimiter to double tick (")
     *
     * @return Config_Lite
     */
    public function setDoubleTickDelimiter()
    {
        $this->delim = '"';
        return $this;
    }

    /**
     * Sets whether or not sections should be processed
     *
     * If true, values for each section will be placed into
     * a sub-array for the section. If false, all values will
     * be placed in the global scope.
     *
     * @param bool $processSections - if true, sections will be processed
     *
     * @return Config_Lite
     */
    public function setProcessSections($processSections)
    {
        $this->processSections = $processSections;
        return $this;
    }

    /**
     * set the line break (newline) chars
     *
     * line-break defaults to Unix Newline "\n",
     * set to support other linebreaks, eg. windows user
     * textfiles "\r\n"
     *
     * @param string $linebreakchars chars
     *
     * @return Config_Lite
     */
    public function setLinebreak($linebreakchars)
    {
        $this->linebreak = $linebreakchars;
        return $this;
    }

    /**
     * generic write ini config file, to save use `save'.
     *
     * writes the global options and sections with normalized Values,
     * that means "bool" values to human readable representation,
     * doublequotes strings and numeric values without any quotes.
     * prepends a php exit if suffix is php,
     * it is valid to write an empty Config file,
     * this method is used by save and is public for explicit usage,
     * eg. if you do not want to hold the whole configuration in the object.
     *
     * @param string $filename      filename
     * @param array  $sectionsarray array with sections
     * @param int    $flags         for file-put-contents
     *
     * @return bool
     * @throws Config_Lite_Exception_Runtime when file is not writeable
     * @throws Config_Lite_Exception_Runtime when write failed
     */
    public function write($filename, $sectionsarray, $flags=null)
    {
        $content = $this->buildOutputString($sectionsarray);
        if (false === file_put_contents($filename, $content, $flags)) {
            throw new Config_Lite_Exception_Runtime(
                sprintf(
                    'failed to write file `%s\' for writing.', $filename
                )
            );
        }
        return true;
    }


}