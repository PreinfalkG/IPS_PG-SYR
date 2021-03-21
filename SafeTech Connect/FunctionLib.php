<?

abstract class LogLevel
{
    const ALL = 9;
    const TEST = 8;
    const TRACE = 7;
    const COMMUNICATION = 6;
    const DEBUG = 5;
    const INFO = 4;
    const WARN = 3;
    const ERROR = 2;
    const FATAL = 1;
}


abstract class VARIABLE
{
    const TYPE_BOOLEAN = 0;
    const TYPE_INTEGER = 1;
    const TYPE_FLOAT = 2;
    const TYPE_STRING = 3;
}


trait SYR_FunctionLib {

    protected function RequestJsonData($url) {

        SetValue($this->GetIDForIdent("requestCnt"), GetValue($this->GetIDForIdent("requestCnt")) + 1); 
        
        $streamContext = stream_context_create( array('http'=> array('timeout' => 5) ) );   //5 seconds Timeout

        $json = file_get_contents($url, false, $streamContext);

        if ($json === false) {
            $error = error_get_last();
            $errorMsg = implode (" | ", $error);
            SetValue($this->GetIDForIdent("ErrorCnt"), GetValue($this->GetIDForIdent("ErrorCnt")) + 1); 
            SetValue($this->GetIDForIdent("LastError"), $errorMsg);

            $logMsg =  sprintf("ERROR %s", $errorMsg);
            if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__FUNCTION__, $logMsg, 0); }
            $logSender = sprintf("%s [%s]", IPS_GetName($this->InstanceID), ($this->InstanceID));
            IPS_LogMessage($logSender, $logMsg);
            die();
        } else {
            SetValue($this->GetIDForIdent("receiveCnt"), GetValue($this->GetIDForIdent("receiveCnt")) + 1);  											
            SetValue($this->GetIDForIdent("LastDataReceived"), time()); 
        }
        return json_decode($json);
    }

    protected function CalcDuration_ms(float $timeStart) {
        $duration =  microtime(true)- $timeStart;
        return round($duration*1000,2);
    }	


}
?>