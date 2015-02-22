<?php

class Config_Lite_ArrayHandler implements Config_Lite_HandlerInterface
{
    function buildOutputString($sectionsarray)
    {
        return print_r($sectionsarray, true);
    }

    function read($filename = null, $mode = 0)
    {
        if (!is_readable($filename)) {
            throw new Config_Lite_Exception_UnexpectedValue("filename not readable");
        }
        $sections = require $filename;
        return $sections;
    }

}
