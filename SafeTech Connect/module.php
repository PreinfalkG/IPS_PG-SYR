<?php
declare(strict_types=1);

require_once("FunctionLib.php");

class SafeTechConnect extends IPSModule {

	use SYR_FunctionLib;

	private $logLevel = 4;
	private $parentRootId;
	private $archivInstanzID;

	public function __construct($InstanceID) {
	
		parent::__construct($InstanceID);		// Diese Zeile nicht löschen

		$this->parentRootId = IPS_GetParent($this->InstanceID);
		$this->archivInstanzID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];

		$currentStatus = $this->GetStatus();
		if($currentStatus == 102) {				//Instanz ist aktiv
			$this->logLevel = $this->ReadPropertyInteger("LogLevel");
			if($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__METHOD__, sprintf("Log-Level is %d", $this->logLevel), 0); }
		} else {
			if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Current Status is '%s'", $currentStatus), 0); }	
		}
	}


	public function Create() {
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyString('SafeTech_IP', "10.0.10.181");
		$this->RegisterPropertyInteger('SafeTech_PORT', 5555);

		$this->RegisterPropertyInteger("LogLevel", 4);
		$this->RegisterPropertyBoolean('AutoUpdate', false);
		$this->RegisterPropertyInteger("UpdateInterval", 30);		
		//$this->RegisterPropertyInteger("UpdateIntervalUnit", 30);	

		$this->RegisterPropertyBoolean("cb_UpdateAB", false);
		$this->RegisterPropertyBoolean("cb_UpdataCND", false);
		$this->RegisterPropertyBoolean("cb_UpdateAVO", false);
		$this->RegisterPropertyBoolean("cb_UpdateVOL", false);
		$this->RegisterPropertyBoolean("cb_UpdatePRF", false);
		$this->RegisterPropertyBoolean("cb_UpdatePV1", false);
		$this->RegisterPropertyBoolean("cb_UpdatePV2", false);

		$this->RegisterTimer('Timer_AutoUpdate', 0, 'STC_Timer_AutoUpdate($_IPS["TARGET"]);');

	}

	public function Destroy() {
		$this->SetUpdateInterval(0);		//Stop Auto-Update Timer
		parent::Destroy();					//Never delete this line!
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		$this->logLevel = $this->ReadPropertyInteger("LogLevel");
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Set Log-Level to %d", $this->logLevel), 0); }

		//$this->RegisterProfiles();
		$this->RegisterVariables();  

		$autoUpdate = $this->ReadPropertyBoolean("AutoUpdate");		
		if($autoUpdate) {
			$autoUpdateInterval = $this->ReadPropertyInteger("UpdateInterval");
			$this->SetUpdateInterval($autoUpdateInterval);
		} else {
			$this->SetUpdateInterval(0);
		}

	}

	public function SetUpdateInterval(int $updateInterval) {
		if ($updateInterval == 0) {  
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Auto-Update stopped [AutoUpdateIntervall = 0]", 0); }	
		} else if ($updateInterval < 5) { 
			$updateInterval = 5; 
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Set Auto-Update Timer Intervall to %s sec", $updateInterval), 0); }	
		} else {
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Set Auto-Update Timer Intervall to %s sec", $updateInterval), 0); }
		}
		$this->SetTimerInterval("Timer_AutoUpdate", $updateInterval*1000);	
	}

	public function Timer_AutoUpdate() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "called ...", 0); }
		$this->Update();
	}

	public function UpdateAB() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Update Absperrung [AB] ..", 0); }	

		$url = "http://10.0.10.181:5555/GetValue";
		$this->RequestJsonData($url);
	}

	public function UpdateCND() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Update Leitwertsensor [CND] ..", 0); }	
	}

	public function UpdateAVO() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Aktuell geflossenes Volumen [AVO] ..", 0); }	
	}

	public function UpdateVOL() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Gesamtvolumen [VOL] ..", 0); }	
	}	

	public function UpdatePRF() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "aktives Profil [PRF] ..", 0); }	
	}		

	public function UpdatePV1() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Leckagestufe Profil 1 [PV1] ..", 0); }	
	}	

	public function UpdatePV2() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Leckagestufe Profil 2 [PV2] ..", 0); }	
	}		

	public function Update($skipUdateSec = 600) {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Update ..", 0); }	

		$lastUpdate  = time() - round(IPS_GetVariable($this->GetIDForIdent("ErrorCnt"))["VariableUpdated"]);
		if ($lastUpdate > $skipUdateSec) {

			$currentStatus = $this->GetStatus();
			if($currentStatus == 102) {		
			
				$start_Time = microtime(true);

				if($this->ReadPropertyBoolean("cb_UpdateAB")) 		{ $this->UpdateAB(); }
				if($this->ReadPropertyBoolean("cb_UpdataCND")) 		{ $this->UpdateCND(); }
				if($this->ReadPropertyBoolean("cb_UpdateAVO")) 		{ $this->UpdateAVO(); }
				if($this->ReadPropertyBoolean("cb_UpdateVOL")) 		{ $this->UpdateVOL(); }
				if($this->ReadPropertyBoolean("cb_UpdatePRF")) 		{ $this->UpdatePRF(); }
				if($this->ReadPropertyBoolean("cb_UpdatePV1"))		{ $this->UpdatePV1(); }
				if($this->ReadPropertyBoolean("cb_UpdatePV2"))		{ $this->UpdatePV2(); }

				$duration = $this->CalcDuration_ms($start_Time);
				SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), $duration); 

			} else {
				SetValue($this->GetIDForIdent("instanzInactivCnt"), GetValue($this->GetIDForIdent("instanzInactivCnt")) + 1);
				if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, sprintf("Instanz '%s - [%s]' not activ [Status=%s]", $this->InstanceID, IPS_GetName($this->InstanceID), $currentStatus), 0); }
			}
		} else {
			SetValue($this->GetIDForIdent("updateSkipCnt"), GetValue($this->GetIDForIdent("updateSkipCnt")) + 1);
			$logMsg =  sprintf("WARNING :: Skip Update for %d sec for Instance '%s' >> last error %d seconds ago...", $skipUdateSec, $this->InstanceID, $lastUpdate);
			if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, $logMsg, 0); }
			$logSender = sprintf("%s [%s]", IPS_GetName($this->InstanceID), ($this->InstanceID));
            IPS_LogMessage($logSender, $logMsg);
		}

	}


	public function ResetCounterVariables() {
		
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, 'RESET Counter Variables', 0); }
		
		SetValue($this->GetIDForIdent("requestCnt"), 0);
		SetValue($this->GetIDForIdent("receiveCnt"), 0);
		SetValue($this->GetIDForIdent("updateSkipCnt"), 0);
		SetValue($this->GetIDForIdent("ErrorCnt"), 0); 
		SetValue($this->GetIDForIdent("LastError"), "-"); 
		SetValue($this->GetIDForIdent("instanzInactivCnt"), 0); 
		SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), 0); 
		SetValue($this->GetIDForIdent("LastDataReceived"), 0); 

	}


    protected function RegisterProfiles() {

    }

	protected function RegisterVariables() {

		$this->RegisterVariableInteger("requestCnt", "Request Cnt", "", 900);
		$this->RegisterVariableInteger("receiveCnt", "Receive Cnt", "", 910);
		$this->RegisterVariableInteger("updateSkipCnt", "Update Skip Cnt", "", 920);	
		$this->RegisterVariableInteger("ErrorCnt", "Error Cnt", "", 930);
		$this->RegisterVariableString("LastError", "Last Error", "", 940);
		$this->RegisterVariableInteger("instanzInactivCnt", "Instanz Inactiv Cnt", "", 950);
		$this->RegisterVariableFloat("lastProcessingTotalDuration", "Last Processing Duration [ms]", "", 960);	
		$this->RegisterVariableInteger("LastDataReceived", "Last Data Received", "~UnixTimestamp", 970);

		$scriptScr = sprintf("<?php STC_Update(%s, 0); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateScript", "Update", $scriptScr, 990);

		IPS_ApplyChanges($this->archivInstanzID);
		if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, "Variables registered", 0); }

	}


	protected function AddLog($methodName, $daten, $format, $enableIPSLogOutput=false) {
		//$this->SendDebug("[" . __CLASS__ . "] - " . $name, $daten, $format); 	
		$this->SendDebug($methodName, $daten, $format); 

		if($enableIPSLogOutput) {
			if($format == 0) {
				IPS_LogMessage("[" . __CLASS__ . "] - " . $name, $daten);	
			} else {
				IPS_LogMessage("[" . __CLASS__ . "] - " . $name, $this->String2Hex($daten));			
			}
		}
	}



}

?>