<?php

interface Config_Lite_HandlerInterface
{
    function read($filename = null, $mode = 0);
    function buildOutputString($sectionsarray);

}