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


trait SafeTech_FunctionLib {

    protected function SetAdminRights() {
        $apiURL = "safe-tec/set/ADM/(2)f";
    }


	protected function CurlGet($url) {
	
        $errorMsg = NULL;

        if($this->logLevel >= LogLevel::COMMUNICATION) { $this->AddLog(__FUNCTION__, "API Request: " . $url); }        
        SetValue($this->GetIDForIdent("requestCnt"), GetValue($this->GetIDForIdent("requestCnt")) + 1); 
        
        $timeStart = microtime(true);
	
	    $ch = curl_init();
   	
        /*
	    $options = array(
	        CURLOPT_URL => $url, 
	        //CURLOPT_HEADER => 0,
			CURLOPT_HTTPHEADER => array('Connection: close'),
	        CURLOPT_RETURNTRANSFER => true,
			//CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			//CURLOPT_USERPWD => "$login:$password",
			CURLOPT_CONNECTTIMEOUT_MS => 600,
	        //CURLOPT_TIMEOUT => 4,
			CURLOPT_TIMEOUT_MS, 600,
			//CURLOPT_FORBID_REUSE => true,     //28.10.2023
	    );
        */

		try {		
		
             //curl_setopt_array($ch, $options);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT , 6);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close')); 	
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		

            $threadDebug = GetValue($this->GetIDForIdent("ThreadDebug"));
            if($threadDebug == 0) {
                SetValue($this->GetIDForIdent("ThreadDebug"), $_IPS['THREAD']); 
            }

			$result =  curl_exec($ch);
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            SetValue($this->GetIDForIdent("ThreadDebug"),  0); 

			if (FALSE === $result) {
				//throw new Exception(curl_error($ch), curl_errno($ch));
                //$errorMsg = sprintf("Curl_ERROR :: %s [%s]", curl_error($ch), curl_errno($ch));
                $errorMsg = sprintf('{ "ERROR" : "curl_exec > %s [%s]" }', curl_error($ch), curl_errno($ch));   

            } else {

                if($httpStatusCode == 200) {	
                    SetValue($this->GetIDForIdent("skipAutoUpdate"), false);
                    SetValue($this->GetIDForIdent("receiveCnt"), GetValue($this->GetIDForIdent("receiveCnt")) + 1);  											
                    SetValue($this->GetIDForIdent("LastDataReceived"), time()); 

                    $result = str_replace("ERROR", "ERR_OR", $result);

                } else {
                    //$errorMsg = sprintf("Curl_WARN :: httpStatusCode >%s< [%s]", $httpStatusCode, $url);
                    $errorMsg = sprintf('{ "ERROR" : "httpStatusCode >%s< [%s]" }', $httpStatusCode, $url);
                }		

            }		

		} catch(Exception $e) {

            //$errorMsg = sprintf("Curl_Exception: %s [%s]", $e->getMessage(), $e->getCode());
            $errorMsg = sprintf('{ "ERROR" : "Exception > %s [%s]" }', $e->getMessage(), $e->getCode());

		} finally {

			//curl_close($ch);      //28.10.2023

            if(!is_null($errorMsg)) {

                SetValue($this->GetIDForIdent("skipAutoUpdate"), true);
                SetValue($this->GetIDForIdent("ErrorCnt"), GetValue($this->GetIDForIdent("ErrorCnt")) + 1); 
                SetValue($this->GetIDForIdent("LastError"), $errorMsg);
    
                if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__FUNCTION__, $errorMsg, 0); }

                //die();
                $result = $errorMsg; 
            }

		}
        
        $duration = round($this->CalcDuration_ms($timeStart), 1);
        
        if($duration > 600) {
            SetValue($this->GetIDForIdent("processingTimeLog"), sprintf("API Response: %s [%s ms]",  $url, $duration)); 
        }
		SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), $duration); 
        
        if($this->logLevel >= LogLevel::COMMUNICATION) { $this->AddLog(__FUNCTION__, sprintf("API Response: %s [%s ms]",  $result, $duration)); }   
        
        $responseInfo =  sprintf("SUMMARY INFO :: %s [%s ms] >> %s\r\n", $url, $duration, $result);
        if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__FUNCTION__, $responseInfo, 0); }   
        //echo $responseInfo;

        return $result;			
	}

    protected function CallRestAPI($url) {

        if($this->logLevel >= LogLevel::COMMUNICATION) { $this->AddLog(__FUNCTION__, "API Request: " . $url, 0); }        
        SetValue($this->GetIDForIdent("requestCnt"), GetValue($this->GetIDForIdent("requestCnt")) + 1); 
        
        $timeStart = microtime(true);

        $result = @file_get_contents($url, false);

        //$streamContext = stream_context_create( array('http'=> array('timeout' => 3) ) );   //5 seconds Timeout
        //$result = @file_get_contents($url, false, $streamContext);

        $duration = $this->CalcDuration_ms($timeStart);
        if($this->logLevel >= LogLevel::COMMUNICATION) { $this->AddLog(__FUNCTION__, sprintf("API Response: %s [%s ms]", $result, $duration)); }   
		SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), $duration); 

        if ($result === false) {
            $error = error_get_last();
            $errorMsg = implode (" | ", $error);
            SetValue($this->GetIDForIdent("skipAutoUpdate"), true);
            SetValue($this->GetIDForIdent("ErrorCnt"), GetValue($this->GetIDForIdent("ErrorCnt")) + 1); 
            SetValue($this->GetIDForIdent("LastError"), $errorMsg);

            $logMsg =  sprintf("ERROR %s", $errorMsg);
            if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__FUNCTION__, $logMsg); }
            //die();
            $result = '{ "ERROR": "HTTP request failed" }';

        } else {
            SetValue($this->GetIDForIdent("skipAutoUpdate"), false);
            SetValue($this->GetIDForIdent("receiveCnt"), GetValue($this->GetIDForIdent("receiveCnt")) + 1);  											
            SetValue($this->GetIDForIdent("LastDataReceived"), time()); 
        }
        return $result;
    }

    protected function CalcDuration_ms(float $timeStart) {
        $duration =  microtime(true) - $timeStart;
        return round($duration*1000,2);
    }	


}
?>