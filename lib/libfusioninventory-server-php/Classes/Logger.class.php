<?php

/** A logging class.
* Examples:
* $log = new Logger("logFilePath");
* $log->notifyDebugMessage("machine $internalId created");
* $log->notifyExceptionMessage("Put your application in the user/applications directory");
*/

class Logger
{
    const EXCEPT = 1;
    const DEBUG = 2;

    private $_fileHandle;

    public function __construct($fileName)
    {
        $filePath = GLPI_DOC_DIR."/_plugins/fusioninventory/logs/";
        if(!file_exists(GLPI_DOC_DIR."/_plugins/fusioninventory/logs/"))
        {
            //mkdir(GLPI_DOC_DIR."/_plugins/fusioninventory/logs/",0777,true);
        }

        $this->_fileHandle = fopen(GLPI_DOC_DIR."/_plugins/fusioninventory/logs", "a");

        if (!is_writable(GLPI_DOC_DIR."/_plugins/fusioninventory/logs"))
        {
            throw new Exception(GLPI_DOC_DIR."/_plugins/fusioninventory/logs isn't writable. Check permissions.");
        }

    }


    public function notifyDebugMessage($line)
    {
        $this->_log($line, Logger::DEBUG);
    }


    public function notifyExceptionMessage($line)
    {
        $this->_log($line, Logger::EXCEPT);
    }


    private function _log($line, $messageType)
    {
        $status = $this->_getStatus($messageType);
        fputs($this->_fileHandle, "$status $line \n");
    }


    private function _getStatus($messageType)
    {
        $time = date("d M Y - H:i:s");

        switch($messageType)
        {
            case Logger::DEBUG:
                $status = "$time - DEBUG -->";
            break;
            case Logger::EXCEPT:
                $status = "$time - EXCEPTION -->";
            break;
            default:
                $status = "$time - LOG   -->";
            break;
        }
        return $status;
    }

}


?>