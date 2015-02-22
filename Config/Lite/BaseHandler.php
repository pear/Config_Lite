<?php

class Config_Lite_BaseHandler
{
    /**
     * _booleans - alias of bool in a representable Configuration String Format
     *
     * @var array
     */
    private $_booleans = array('1' => true, 'on' => true,
        'true' => true, 'yes' => true,
        '0' => false, 'off' => false,
        'false' => false, 'no' => false);


    /**
     * string delimiter
     *
     * @var string
     */
    protected $delim = '"';


    /**
     * detect Type "bool" by String Value to keep those "untouched"
     *
     * @param string $value value
     *
     * @return bool
     */
    protected function isBool($value)
    {
        return in_array($value, $this->_booleans);
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