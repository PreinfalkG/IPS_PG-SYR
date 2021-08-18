<?php
declare(strict_types=1);

require_once("FunctionLib.php");
include_once("CommandSetConfig.php");

const SKIP_AutoUpdate_Duration = 600;
const RESET_UpdateInProcess_Duration = 240;

class SafeTechConnect extends IPSModule {

	use SafeTech_CommandSet;
	use SafeTech_FunctionLib;

	private $logLevel = 4;
	private $parentRootId;
	private $archivInstanzID;

	private $baseApiURL;
	private $apiUserLevel;
	private $commandSetConfigArr;

	private $ErrorCnt = 0;

	public function __construct($InstanceID) {
	
		parent::__construct($InstanceID);		// Diese Zeile nicht löschen

		$this->parentRootId = IPS_GetParent($this->InstanceID);
		$this->archivInstanzID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];

		$this->UpdateInProcess = false;
		$this->apiUserLevel = 0;
		$this->commandSetConfigArr = $this->GetCommandSetConfigArr();

		//if (IPS_GetKernelRunlevel() == 10103) {

			$currentStatus = $this->GetStatus();
			if($currentStatus == 102) {				//Instanz ist aktiv
				$this->logLevel = $this->ReadPropertyInteger("LogLevel");
				if($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__METHOD__, sprintf("Log-Level is %d", $this->logLevel), 0); }
				$ip = $this->ReadPropertyString("SafeTech_IP");
				$port = $this->ReadPropertyInteger("SafeTech_PORT");
				$this->baseApiURL = sprintf("http://%s:%s", $ip, $port);
			} else {
				if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Current Status is '%s'", $currentStatus), 0); }	
			}
		//}
	}


	public function Create() {
		//Never delete this line!
		parent::Create();

		$this->RegisterPropertyString('SafeTech_IP', "10.0.10.181");
		$this->RegisterPropertyInteger('SafeTech_PORT', 5333);

		$this->RegisterPropertyInteger("LogLevel", 4);
		$this->RegisterPropertyBoolean('AutoUpdate', false);
		$this->RegisterPropertyInteger("UpdateInterval", 30);		

		$this->RegisterPropertyBoolean("cb_UpdatePRF", false);
		$this->RegisterPropertyBoolean("cb_UpdateAB", false);
		$this->RegisterPropertyBoolean("cb_UpdataCEL", false);
		$this->RegisterPropertyBoolean("cb_UpdateBAR", false);
		$this->RegisterPropertyBoolean("cb_UpdateCND", false);
		$this->RegisterPropertyBoolean("cb_UpdateFLO", false);
		$this->RegisterPropertyBoolean("cb_UpdateLTV", false);
		$this->RegisterPropertyBoolean("cb_UpdateVOL", false);
		$this->RegisterPropertyBoolean("cb_UpdateAVO", false);
		$this->RegisterPropertyBoolean("cb_UpdateBAT", false);
		$this->RegisterPropertyBoolean("cb_UpdateNET", false);
		$this->RegisterPropertyBoolean("cb_UpdateALA", false);

		$this->RegisterPropertyBoolean("cb_UpdateGroupAlarm", false);
		$this->RegisterPropertyBoolean("cb_UpdateGroupMeasurements", false);
		$this->RegisterPropertyBoolean("cb_UpdateGroupProfile", false);
		$this->RegisterPropertyBoolean("cb_UpdateGroupNetwork", false);
		$this->RegisterPropertyBoolean("cb_UpdateGroupSettings", false);						

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

		$this->RegisterProfiles();
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
		if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, "called ...", 0); }
		$skipAutoUpdate = GetValue($this->GetIDForIdent("skipAutoUpdate"));
		if($skipAutoUpdate) {
			$lastChanged  = time() - round(IPS_GetVariable($this->GetIDForIdent("skipAutoUpdate"))["VariableChanged"]);
			if ($lastChanged >= SKIP_AutoUpdate_Duration) {
				SetValue($this->GetIDForIdent("skipAutoUpdate"), false);
				$this->DoUpdates();
			} else {
				SetValue($this->GetIDForIdent("updateSkipCnt"), GetValue($this->GetIDForIdent("updateSkipCnt")) + 1);

				$lastError  = time() - round(IPS_GetVariable($this->GetIDForIdent("ErrorCnt"))["VariableChanged"]);
				$logMsg =  sprintf("WARNING :: Skip Auto Update for %d sec for Instance '%s' >> last error %d seconds ago...", SKIP_AutoUpdate_Duration, $this->InstanceID, $lastError);
				if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, $logMsg, 0); } 

				$autoUpdateInterval = $this->ReadPropertyInteger("UpdateInterval");
				if($lastChanged < ($autoUpdateInterval * 1.1)) {
					$logSender = sprintf("%s [%s]", IPS_GetName($this->InstanceID), ($this->InstanceID));
					IPS_LogMessage($logSender, $logMsg);				
				}
			} 
		} else {
			$this->DoUpdates();
		}
	}

	public function DoUpdates() {
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Update ..", 0); }	

		$currentStatus = $this->GetStatus();
		if($currentStatus == 102) {		
		
			$start_Time = microtime(true);

			if($this->ReadPropertyBoolean("cb_UpdatePRF")) 		{ $this->Update("PRF"); }
			if($this->ReadPropertyBoolean("cb_UpdateAB")) 		{ $this->Update("AB"); }
			if($this->ReadPropertyBoolean("cb_UpdataCEL")) 		{ $this->Update("CEL"); }
			if($this->ReadPropertyBoolean("cb_UpdateBAR")) 		{ $this->Update("BAR"); }
			if($this->ReadPropertyBoolean("cb_UpdateCND")) 		{ $this->Update("CND"); }
			if($this->ReadPropertyBoolean("cb_UpdateFLO")) 		{ $this->Update("FLO"); }
			if($this->ReadPropertyBoolean("cb_UpdateLTV"))		{ $this->Update("LTV"); }
			if($this->ReadPropertyBoolean("cb_UpdateVOL"))		{ $this->Update("VOL"); }
			if($this->ReadPropertyBoolean("cb_UpdateAVO")) 		{ $this->Update("AVO"); }
			if($this->ReadPropertyBoolean("cb_UpdateBAT")) 		{ $this->Update("BAT"); }
			if($this->ReadPropertyBoolean("cb_UpdateNET"))		{ $this->Update("NET"); }
			if($this->ReadPropertyBoolean("cb_UpdateALA"))		{ $this->Update("ALA"); }

			if($this->ReadPropertyBoolean("cb_UpdateGroupAlarm"))			{ $this->UpdateGroup("Alarm"); }
			if($this->ReadPropertyBoolean("cb_UpdateGroupMeasurements"))	{ $this->UpdateGroup("Measurement"); }
			if($this->ReadPropertyBoolean("cb_UpdateGroupProfile"))			{ $this->UpdateGroup("Profile"); }
			if($this->ReadPropertyBoolean("cb_UpdateGroupNetwork"))			{ $this->UpdateGroup("Network"); }
			if($this->ReadPropertyBoolean("cb_UpdateGroupSettings"))		{ $this->UpdateGroup("Settings"); }

			$duration = $this->CalcDuration_ms($start_Time);
			SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), $duration); 

		} else {
			SetValue($this->GetIDForIdent("instanzInactivCnt"), GetValue($this->GetIDForIdent("instanzInactivCnt")) + 1);
			if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, sprintf("Instanz '%s - [%s]' not activ [Status=%s]", $this->InstanceID, IPS_GetName($this->InstanceID), $currentStatus), 0); }
		}

	}

	public function Get(string $key) {
		$this->GetAndUpdateVariable($key, false);
	}

	public function Update(string $key) {
		$this->GetAndUpdateVariable($key, true);
	}

	public function UpdateALL() { 
		$this->UpdateGroup("ALL");
	}

	public function UpdateGroup(string $groupNameToUpdate) { 

		$updateInProcess = GetValue($this->GetIDForIdent("updateInProcess"));
		if($updateInProcess) {

			$lastChanged  = time() - round(IPS_GetVariable($this->GetIDForIdent("updateInProcess"))["VariableChanged"]);
			if ($lastChanged >= RESET_UpdateInProcess_Duration) {
				SetValue($this->GetIDForIdent("updateInProcess"), false);
			}

		} else {
			SetValue($this->GetIDForIdent("updateInProcess"), true);
			$timeStart = microtime(true);
			$cnt=0;
			foreach($this->commandSetConfigArr as $key=>$configArrElem) {
				$name = $configArrElem[ConfigArrOffset_Name];
				$groupId = $configArrElem[ConfigArrOffset_GroupId];
				$groupName = $configArrElem[ConfigArrOffset_GroupName];

				//echo $groupNameToUpdate . " " . $groupName . "\r\n";
				if(($groupNameToUpdate == "ALL") OR ($groupNameToUpdate == $groupName)) {
					if($groupId > 0) {
						$cnt++;
						//ips6-- set_time_limit(15);	disabled in IPC v6.0
						$this->GetAndUpdateVariable($key, true);
						IPS_Sleep(40);
					}
				}
			}
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf("Update DONE for %d Parameters [%s ms]. ErrorCnt = %d",  $cnt, $this->CalcDuration_ms($timeStart), $this->ErrorCnt), 0);  }
			SetValue($this->GetIDForIdent("updateInProcess"), false);
		}
	}

	public function UpdateGroups(array $arrGroupIds) { 

		SetValue($this->GetIDForIdent("requestCnt"), 0);
		SetValue($this->GetIDForIdent("receiveCnt"), 0);
		SetValue($this->GetIDForIdent("skipAutoUpdate"), false);
		SetValue($this->GetIDForIdent("updateSkipCnt"), 0);
		SetValue($this->GetIDForIdent("ErrorCnt"), 0); 
		SetValue($this->GetIDForIdent("LastError"), "-"); 
		SetValue($this->GetIDForIdent("instanzInactivCnt"), 0); 
		SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), 0); 
		SetValue($this->GetIDForIdent("LastDataReceived"), 0); 


		$timeStart = microtime(true);
		$cnt=0;
		foreach($this->commandSetConfigArr as $key=>$configArrElem) {
			
			$name = $configArrElem[ConfigArrOffset_Name];
			$groupId = $configArrElem[ConfigArrOffset_GroupId];
			$groupName = $configArrElem[ConfigArrOffset_GroupName];

			if(in_array($groupId, $arrGroupIds, true)) {
				if($groupId > 0) {
					$cnt++;
					//ips6-- set_time_limit(15);
					$this->GetAndUpdateVariable($key, true);
					IPS_Sleep(50);
				}
			}
		}
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__FUNCTION__, sprintf("Update DONE for %d Parameters [%s ms]. ErrorCnt = %d",  $cnt, $this->CalcDuration_ms($timeStart), $this->ErrorCnt), 0);  }
	}


	protected function GetAndUpdateVariable($key, $updateVariable=false) {

		$returnValue = -9999;
		$responseKey = "get". $key;
		
		if(array_key_exists($key, $this->commandSetConfigArr)) {
			$configArrElem = $this->commandSetConfigArr[$key];

			$userLevel = $configArrElem[ConfigArrOffset_UserLevel];	
			$varGroup = $configArrElem[ConfigArrOffset_GroupName];
			
			$logMsg = sprintf("%s | Level: %s | Group: %s", $responseKey, $userLevel, $varGroup);
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, $logMsg, 0); }

			$apiURL = $this->baseApiURL . "/safe-tec/get/" . $key;	
			
			//$apiResponse = $this->CallRestAPI($apiURL);	
			$apiResponse = $this->CurlGet($apiURL);	
			if($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__METHOD__, $apiResponse, 0); }


			if (strpos($apiResponse, "ERROR: ADM") !== false) {

				if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "Need higher API Rights > Request 'FACTORY/(ADMIN) Rights ...", 0); }
				$this->SetApiUserLevel(2);
				SetValue($this->GetIDForIdent("grantAdminRightsCnt"), GetValue($this->GetIDForIdent("grantAdminRightsCnt")) + 1); 

				$apiResponse = $this->CurlGet($apiURL);	
				if($this->logLevel >= LogLevel::TRACE) { $this->AddLog(__METHOD__, $apiResponse, 0); }

			}


			if (strpos($apiResponse, "ERROR") !== false) {
				//if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__METHOD__, sprintf("ERROR :: Response for Request '%s' is '%s'", $key, $apiValue), 0); }
				if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__METHOD__, sprintf("%s [UserLevel in Config: %s]", $apiResponse, $userLevel), 0); }
				$this->ErrorCnt++;
			} else {

				$json = json_decode($apiResponse);
				if(isset($json->$responseKey)) {
					$apiValue = $json->$responseKey;

					$varType = $configArrElem[ConfigArrOffset_VarType];
					switch($varType) {
						case VARIABLE::TYPE_BOOLEAN:
							$returnValue = boolval($apiValue);
							if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Parse apiValue '%s' to Boolean |%s|", $apiValue, $returnValue), 0); }
							break;
						case VARIABLE::TYPE_INTEGER:
							$apiValue = preg_replace('~\D~', '', $apiValue);
							$returnValue = intval($apiValue);
							$returnValue = $returnValue * $configArrElem[ConfigArrOffset_Multiplikator];
							if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Parse apiValue '%s' to Integer |%s|", $apiValue, $returnValue), 0); }
							break;
						case VARIABLE::TYPE_FLOAT:
							$returnValue = floatval(str_replace(',', '.', str_replace('.', '', $apiValue)));
							$returnValue = $returnValue * $configArrElem[ConfigArrOffset_Multiplikator];
							if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Parse apiValue '%s' to Float |%s|", $apiValue, $returnValue), 0); }
							break;
						case VARIABLE::TYPE_STRING:
						default:
							$returnValue = print_r($apiValue, true);
							if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("apiValue String |%s|", $returnValue), 0); }
							break;															
					}

					if($updateVariable) {
						if($key == "ALA") {
							$this->UpdateVariable($key, $returnValue, $configArrElem);
							$this->UpdateVariable("ALAi", hexdec($returnValue), $this->commandSetConfigArr["ALAi"]);
						} else if ($key == "CND") {
							$this->UpdateVariable($key, $returnValue, $configArrElem);
							$this->UpdateVariable("dH", round($returnValue / 30, 1), $this->commandSetConfigArr["dH"]);
						} else if ($key == "NET") {
							$this->UpdateVariable($key, round($returnValue, 1), $configArrElem);
						} else if ($key == "BAT") {
							$this->UpdateVariable($key, round($returnValue, 1), $configArrElem);
						} else {
							$this->UpdateVariable($key, $returnValue, $configArrElem);
						}
					}

				} else {
					if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, sprintf("WARN :: RequestKey '%s' does not exist in Response '%s'", $responseKey,  $apiResponse), 0); }
					return $returnValue;
				}

			}
			return $returnValue;
			
		} else {
			if($this->logLevel >= LogLevel::WARN) { $this->AddLog(__METHOD__, sprintf("WARN :: ConfigArrayKey '%s' does not exist	", $key), 0); }
			return $returnValue;
		}

	}


	protected function UpdateVariable($key, $value, $configArr) {

		$name = $configArr[ConfigArrOffset_Name];
		$groupId = $configArr[ConfigArrOffset_GroupId];
		$groupName = $configArr[ConfigArrOffset_GroupName];
	
		$instanzId = @IPS_GetObjectIDByIdent($groupName, $this->parentRootId);
		if($instanzId === false) {
			$instanzId = IPS_CreateInstance("{485D0419-BE97-4548-AA9C-C083EB82E61E}");
			IPS_SetIdent($instanzId, $groupName);
			IPS_SetName($instanzId, $groupName);
			IPS_SetParent($instanzId,  $this->parentRootId);
			IPS_SetPosition($instanzId, $groupId);
			if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Instance '%s' created with ID '%s'", $groupName, $instanzId), 0); }
		}

        $varId = @IPS_GetObjectIDByIdent($key, $instanzId);
        if ($varId === false) {

			$varType =  $configArr[ConfigArrOffset_VarType];
			$varPos =  $configArr[ConfigArrOffset_VarPosition];
			$profileName = $configArr[ConfigArrOffset_VarProfile];
			$varEnabeAC = $configArr[ConfigArrOffset_VarEnabelAC];
			$description = $configArr[ConfigArrOffset_Description];
           
            $varId = IPS_CreateVariable($varType); //0 - Boolean | 1-Integer | 2 - Float | 3 - String
			IPS_SetIdent($varId, $key);
            IPS_SetName($varId, $name);
            IPS_SetParent($varId, $instanzId);
			IPS_SetPosition($varId, $varPos);
            if($profileName != "") { 
                $return = @IPS_SetVariableCustomProfile($varId, $profileName); 
                if(!$return) { 
                    if($this->logLevel >= LogLevel::ERROR) { $this->AddLog(__METHOD__, sprintf("ERROR setting Profile '%s' to varID %s", $profileName, $varId), 0); }
                }
            }
            if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Variable '%s' created for '%s - %s'", $varId, $key, $name), 0); }
        } else {
            if($this->logLevel >= LogLevel::DEBUG) { $this->AddLog(__METHOD__, sprintf("Found Variable '%s' for '%s'", $varId, $key), 0); }
        }

		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("Set Value '%s' for Variable '%s - %s'", print_r($value, true), $varId, IPS_GetName($varId)), 0); }

		SetValue($varId, $value);
    }

	public function SetApiUserLevel(int $level = 0) {
		//0 = USER (Standard)	> clr/adm
		//1 = SERVICE			> set/adm/(1)
		//2 = FACTORY (ADMIN)	> set/adm/(2)f

		$apiURL = $this->baseApiURL . "/safe-tec/clr/ADM";
		switch($level) {
			case 0:
				$apiURL = $this->baseApiURL . "/safe-tec/clr/ADM";	
				break;
			case 1:
				$apiURL = $this->baseApiURL . "/safe-tec/set/ADM/(1)";
				break;
			case 2:
				$apiURL = $this->baseApiURL . "/safe-tec/set/ADM/(2)f";
				break;
			default:
				$apiURL = $this->baseApiURL . "/safe-tec/clr/ADM";
				break;
		}

		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("SET API UserLevel > %s", $apiURL), 0); }
		$apiResponse = $this->CurlGet($apiURL);	
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, $apiResponse, 0); }

	}


	public function OpenShutoff() {

		// Absperrung (Shutoff) | 1 Opened | 2 Closed
		$apiURL = $this->baseApiURL . "/safe-tec/set/ab/1";

		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "OPEN Shutoff will be executed ...", 0); }
		$apiResponse = $this->CurlGet($apiURL);	
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, $apiResponse, 0); }		

	}

	public function CloseShutoff() {

		// Absperrung (Shutoff) | 1 Opened | 2 Closed
		$apiURL = $this->baseApiURL . "/safe-tec/set/ab/2";

		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, "CLOSE Shutoff will be executed ...", 0); }
		$apiResponse = $this->CurlGet($apiURL);	
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, $apiResponse, 0); }

	}


	public function SetAktivProfile(int $profileNr = 2) {

		// 1 = Anwesend
		// 2 = Abwesend
		// 3-8 = Custom Prfile

		if( ($profileNr < 1 ) OR ($profileNr > 8) ) {
			$profileNr = 2;
		}

		$apiURL = $this->baseApiURL . sprintf("/safe-tec/set/PRF/%d", $profileNr);
		
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, sprintf("SET Aktiv Profile to '%d' > %s", $profileNr, $apiURL), 0); }
		$apiResponse = $this->CurlGet($apiURL);	
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, $apiResponse, 0); }

		$this->UpdateGroup("Profile");
		//$this->GetAndUpdateVariable("PRF", true);		
		//return $apiResponse;
	}


	public function ResetCounterVariables() {
		
		if($this->logLevel >= LogLevel::INFO) { $this->AddLog(__METHOD__, 'RESET Counter Variables', 0); }
		
		SetValue($this->GetIDForIdent("requestCnt"), 0);
		SetValue($this->GetIDForIdent("receiveCnt"), 0);
		SetValue($this->GetIDForIdent("updateInProcess"), false);
		SetValue($this->GetIDForIdent("skipAutoUpdate"), false);
		SetValue($this->GetIDForIdent("updateSkipCnt"), 0);
		SetValue($this->GetIDForIdent("grantAdminRightsCnt"), 0); 
		SetValue($this->GetIDForIdent("ErrorCnt"), 0); 
		SetValue($this->GetIDForIdent("LastError"), "-"); 
		SetValue($this->GetIDForIdent("instanzInactivCnt"), 0); 
		SetValue($this->GetIDForIdent("processingTimeLog"), "-"); 
		SetValue($this->GetIDForIdent("lastProcessingTotalDuration"), 0); 
		SetValue($this->GetIDForIdent("LastDataReceived"), 0); 

	}


    protected function RegisterProfiles() {

		if ( !IPS_VariableProfileExists('SYR.DisabledEnabled') ) {
            IPS_CreateVariableProfile('SYR.DisabledEnabled', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.DisabledEnabled', "", "" );
            IPS_SetVariableProfileAssociation('SYR.DisabledEnabled', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.DisabledEnabled', 1, "[%d] Enabled", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.ActiveDeactivated') ) {
            IPS_CreateVariableProfile('SYR.ActiveDeactivated', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.ActiveDeactivated', "", "" );
            IPS_SetVariableProfileAssociation('SYR.ActiveDeactivated', 0, "[%d] Active", "", -1);
            IPS_SetVariableProfileAssociation('SYR.ActiveDeactivated', 1, "[%d] Deactivated", "", -1);
        }	
		
		if ( !IPS_VariableProfileExists('SYR.InactiveActive') ) {
            IPS_CreateVariableProfile('SYR.InactiveActive', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.InactiveActive', "", "" );
            IPS_SetVariableProfileAssociation('SYR.InactiveActive', 0, "[%d] inactive", "", -1);
            IPS_SetVariableProfileAssociation('SYR.InactiveActive', 1, "[%d] active", "", -1);
        }			

        if ( !IPS_VariableProfileExists('SYR.Hour') ) {
            IPS_CreateVariableProfile('SYR.Hour', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Hour', "", " h" );
        } 

		if ( !IPS_VariableProfileExists('SYR.Minute') ) {
            IPS_CreateVariableProfile('SYR.Minute', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Minute', "", " min" );
        } 
		
		if ( !IPS_VariableProfileExists('SYR.Seconds') ) {
            IPS_CreateVariableProfile('SYR.Seconds', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Seconds', "", " sec" );
        } 

		if ( !IPS_VariableProfileExists('SYR.Absperrung') ) {
            IPS_CreateVariableProfile('SYR.Absperrung', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Absperrung', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Absperrung', 0, "[%d] n.a.", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Absperrung', 1, "[%d] Opened", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Absperrung', 2, "[%d] Closed", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Absperrung', 3, "[%d] n.a.", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.Profile.VolumeLevel') ) {
            IPS_CreateVariableProfile('SYR.Profile.VolumeLevel', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Profile.VolumeLevel', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Profile.VolumeLevel', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Profile.VolumeLevel', 1, "%d l", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.Profile.TimeLevel') ) {
            IPS_CreateVariableProfile('SYR.Profile.TimeLevel', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Profile.TimeLevel', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Profile.TimeLevel', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Profile.TimeLevel', 1, "%d min", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.Profile.MaxFlow') ) {
            IPS_CreateVariableProfile('SYR.Profile.MaxFlow', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Profile.MaxFlow', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Profile.MaxFlow', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Profile.MaxFlow', 1, "%d l/h", "", -1);
        }


		if ( !IPS_VariableProfileExists('SYR.TMP') ) {
            IPS_CreateVariableProfile('SYR.TMP', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.TMP', "", "" );
            IPS_SetVariableProfileAssociation('SYR.TMP', 0, "[%d] temporary deactivation disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.TMP', 1, "deactivated for %d seconds", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.Language') ) {
            IPS_CreateVariableProfile('SYR.Language', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Language', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Language', 0, "[%d] DE", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Language', 1, "[%d] EN", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Language', 2, "[%d] ES", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Language', 3, "[%d] IT", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Language', 4, "[%d] PL", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Language', 5, "[%d] unknown", "", -1);
        }		


		if ( !IPS_VariableProfileExists('SYR.Units') ) {
            IPS_CreateVariableProfile('SYR.Units', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Units', "", "" );
            IPS_SetVariableProfileAssociation('SYR.Units', 0, "[%d] °C/bar/Liter", "", -1);
            IPS_SetVariableProfileAssociation('SYR.Units', 1, "[%d] °F/psi/US.liq.gal", "", -1);
			IPS_SetVariableProfileAssociation('SYR.Units', 5, "[%d] unknown", "", -1);
        }	


		if ( !IPS_VariableProfileExists('SYR.DMA') ) {
            IPS_CreateVariableProfile('SYR.DMA', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.DMA', "", "" );
            IPS_SetVariableProfileAssociation('SYR.DMA', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.DMA', 1, "[%d] Warning", "", -1);
			IPS_SetVariableProfileAssociation('SYR.DMA', 2, "[%d] Shutoff", "", -1);
			IPS_SetVariableProfileAssociation('SYR.DMA', 5, "[%d] unknown", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.DRP') ) {
            IPS_CreateVariableProfile('SYR.DRP', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.DRP', "", "" );
            IPS_SetVariableProfileAssociation('SYR.DRP', 0, "[%d] Always", "", -1);
            IPS_SetVariableProfileAssociation('SYR.DRP', 1, "[%d] Day", "", -1);
			IPS_SetVariableProfileAssociation('SYR.DRP', 2, "[%d] Week", "", -1);
			IPS_SetVariableProfileAssociation('SYR.DRP', 3, "[%d] Month", "", -1);
			IPS_SetVariableProfileAssociation('SYR.DRP', 5, "[%d] unknown", "", -1);
        }	


		if ( !IPS_VariableProfileExists('SYR.CNL') ) {
            IPS_CreateVariableProfile('SYR.CNL', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.CNL', "", "" );
            IPS_SetVariableProfileAssociation('SYR.CNL', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.CNL', 1, "%d uS/cm", "", -1);
        }

		if ( !IPS_VariableProfileExists('SYR.CNF') ) {
            IPS_CreateVariableProfile('SYR.CNF', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.CNF', "", "" );
            IPS_SetVariableProfileAssociation('SYR.CNF', 0, "[%d] Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.CNF', 1, "%d min", "", -1);
        }		


		if ( !IPS_VariableProfileExists('SYR.Percent') ) {
            IPS_CreateVariableProfile('SYR.Percent', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Percent', "", " %" );
        } 


        if ( !IPS_VariableProfileExists('SYR.Voltage.1') ) {
            IPS_CreateVariableProfile('SYR.Voltage.1', VARIABLE::TYPE_FLOAT);
            IPS_SetVariableProfileDigits('SYR.Voltage.1', 1 );
            IPS_SetVariableProfileText('SYR.Voltage.1', "", " V" );
            //IPS_SetVariableProfileValues('SYR.Voltage.1', 0, 0, 0);
        } 

        if ( !IPS_VariableProfileExists('SYR.Voltage.2') ) {
            IPS_CreateVariableProfile('SYR.Voltage.2', VARIABLE::TYPE_FLOAT);
            IPS_SetVariableProfileDigits('SYR.Voltage.2', 2 );
            IPS_SetVariableProfileText('SYR.Voltage.2', "", " V" );
            //IPS_SetVariableProfileValues('SYR.Voltage.2', 0, 0, 0);
        } 

        if ( !IPS_VariableProfileExists('SYR.Temp') ) {
            IPS_CreateVariableProfile('SYR.Temp', VARIABLE::TYPE_FLOAT);
            IPS_SetVariableProfileDigits('SYR.Temp', 1 );
            IPS_SetVariableProfileText('SYR.Temp', "", " °C" );
            //IPS_SetVariableProfileValues('SYR.Voltage.2', 0, 0, 0);
        } 		

		if ( !IPS_VariableProfileExists('SYR.mbar') ) {
            IPS_CreateVariableProfile('SYR.mbar', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.mbar', "", " mbar" );
        } 

		if ( !IPS_VariableProfileExists('SYR.Conductivity') ) {
            IPS_CreateVariableProfile('SYR.Conductivity', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Conductivity', "", " uS/cm" );
        } 

		if ( !IPS_VariableProfileExists('SYR.WaterFlow') ) {
            IPS_CreateVariableProfile('SYR.WaterFlow', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.WaterFlow', "", " l/h" );
        } 
		
		if ( !IPS_VariableProfileExists('SYR.Liter') ) {
            IPS_CreateVariableProfile('SYR.Liter', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Liter', "", " Liter" );
        } 		

		if ( !IPS_VariableProfileExists('SYR.Milliliter') ) {
            IPS_CreateVariableProfile('SYR.Milliliter', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.Milliliter', "", " mL" );
        } 	

		if ( !IPS_VariableProfileExists('SYR.WFS') ) {
            IPS_CreateVariableProfile('SYR.WFS', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.WFS', "", "" );
            IPS_SetVariableProfileAssociation('SYR.WFS', 0, "[%d] Disconnected", "", -1);
            IPS_SetVariableProfileAssociation('SYR.WFS', 1, "[%d] Connecting", "", -1);
			IPS_SetVariableProfileAssociation('SYR.WFS', 2, "[%d] Connected", "", -1);
			IPS_SetVariableProfileAssociation('SYR.WFS', 5, "[%d] unknown", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.MQT') ) {
            IPS_CreateVariableProfile('SYR.MQT', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.MQT', "", "" );
            IPS_SetVariableProfileAssociation('SYR.MQT', 0, "no MQTT (HTTPS)", "", -1);
            IPS_SetVariableProfileAssociation('SYR.MQT', 1, "[%d] MQTT", "", -1);
			IPS_SetVariableProfileAssociation('SYR.MQT', 5, "[%d] unknown", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.MRT') ) {
            IPS_CreateVariableProfile('SYR.MRT', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.MRT', "", "" );
            IPS_SetVariableProfileAssociation('SYR.MRT', 0, "disabled (default time 7h)", "", -1);
            IPS_SetVariableProfileAssociation('SYR.MRT', 1, "%d min", "", -1);
        }			

		if ( !IPS_VariableProfileExists('SYR.VLV') ) {
            IPS_CreateVariableProfile('SYR.VLV', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.VLV', "", "" );
            IPS_SetVariableProfileAssociation('SYR.VLV', 10, "[%d] Closed", "", -1);
            IPS_SetVariableProfileAssociation('SYR.VLV', 11, "[%d] Closing", "", -1);
			IPS_SetVariableProfileAssociation('SYR.VLV', 20, "[%d] Open", "", -1);
			IPS_SetVariableProfileAssociation('SYR.VLV', 21, "[%d] Opening", "", -1);
			IPS_SetVariableProfileAssociation('SYR.VLV', 30, "[%d] Undefined", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.Bar') ) {
            IPS_CreateVariableProfile('SYR.Bar', VARIABLE::TYPE_FLOAT);
            IPS_SetVariableProfileText('SYR.Bar', "", " bar" );
        } 	

		if ( !IPS_VariableProfileExists('SYR.SLP') ) {
            IPS_CreateVariableProfile('SYR.SLP', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.SLP', "", "" );
            IPS_SetVariableProfileAssociation('SYR.SLP', 0, "Disabled", "", -1);
            IPS_SetVariableProfileAssociation('SYR.SLP', 1, "Day [%d]", "", -1);
        }		

		if ( !IPS_VariableProfileExists('SYR.ALD') ) {
            IPS_CreateVariableProfile('SYR.ALD', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.ALD', "", "" );
            IPS_SetVariableProfileAssociation('SYR.ALD', 10, "[%d] Standard", "", -1);
            IPS_SetVariableProfileAssociation('SYR.ALD', 11, "Rotated [%d]°", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.SRO') ) {
            IPS_CreateVariableProfile('SYR.SRO', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.SRO', "", "" );
            IPS_SetVariableProfileAssociation('SYR.SRO', 10, "[%d] Unlimited", "", -1);
            IPS_SetVariableProfileAssociation('SYR.SRO', 11, "[%d] sec", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.CLP') ) {
            IPS_CreateVariableProfile('SYR.CLP', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.CLP', "", "" );
            IPS_SetVariableProfileAssociation('SYR.CLP', 0, "[%d] Shows profile name", "", -1);
            IPS_SetVariableProfileAssociation('SYR.CLP', 1, "[%d] Shows 'Cluster' instead of profile name", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.BPB') ) {
            IPS_CreateVariableProfile('SYR.BPB', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.BPB', "", "" );
            IPS_SetVariableProfileAssociation('SYR.BPB', 0, "[%d] Profile change blocked", "", -1);
            IPS_SetVariableProfileAssociation('SYR.BPB', 1, "[%d] Profile change possible", "", -1);
        }	


		if ( !IPS_VariableProfileExists('SYR.SFV') ) {
            IPS_CreateVariableProfile('SYR.SFV', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.SFV', "", "" );
            IPS_SetVariableProfileAssociation('SYR.SFV', 0, "[%d] new firmware not available", "", -1);
            IPS_SetVariableProfileAssociation('SYR.SFV', 1, "[%d] new firmware available", "", -1);
        }	


		if ( !IPS_VariableProfileExists('SYR.AlarmCodes') ) {
            IPS_CreateVariableProfile('SYR.AlarmCodes', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.AlarmCodes', "", "" );
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 160, "[%d] unknown", "", -1);
            IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 161, "ALARM END SWITCH [A1 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 162, "NO NETWORK [A2 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 163, "ALARM VOLUME LEAKAGE [A3 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 164, "ALARM TIME LEAKAGE [A4 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 165, "ALARM MAX FLOW LEAKAGE [A5 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 166, "ALARM MICRO LEAKAGE [A6 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 167, "ALARM EXT. SENSOR LEAKAGE [A7 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 168, "ALARM TURBINE BLOCKED [A8 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 169, "ALARM PRESSURE SENSOR ERROR [A9 - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 170, "ALARM TEMPERATURE SENSOR ERROR [AA - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 171, "ALARM CONDUCTIVITY SENSOR ERROR [AB - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 172, "ALARM TO HIGH CONDUCTIVITY [AC - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 173, "LOW BATTERY [AD - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 174, "WARNING VOLUME LEAKAGE [AE - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 175, "ALARM NO POWER SUPPLY [AF - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 254, "[%d] unknown", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 255, "NO ALARM [FF - %d]", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AlarmCodes', 256, "[%d] unknown", "", -1);
        }	

		if ( !IPS_VariableProfileExists('SYR.dH') ) {
            IPS_CreateVariableProfile('SYR.dH', VARIABLE::TYPE_FLOAT);
            IPS_SetVariableProfileText('SYR.dH', "", "" );
            IPS_SetVariableProfileAssociation('SYR.dH', 0, 		"%.1f °dH [sehr weich]", "", -1);			// [0-4]
            IPS_SetVariableProfileAssociation('SYR.dH', 4.01, 	"%.1f °dH [weich]", "", -1);				// [4-9]
			IPS_SetVariableProfileAssociation('SYR.dH', 9.01, 	"%.1f °dH [leicht hart]", "", -1);			// [9-15]
			IPS_SetVariableProfileAssociation('SYR.dH', 15.01, 	"%.1f °dH [mäßig hart]", "", -1);			// [15-19]
			IPS_SetVariableProfileAssociation('SYR.dH', 19.01, 	"%.1f °dH [hart]", "", -1);					// [19-25]
			IPS_SetVariableProfileAssociation('SYR.dH', 25.01, 	"%.1f °dH [sehr hart]", "", -1);			// [über 25]
        }	

		if ( !IPS_VariableProfileExists('SYR.AktivProfile') ) {
            IPS_CreateVariableProfile('SYR.AktivProfile', VARIABLE::TYPE_INTEGER);
            IPS_SetVariableProfileText('SYR.AktivProfile', "", "" );
            IPS_SetVariableProfileAssociation('SYR.AktivProfile', 0, 	"[%d] unknown", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 1, 	"[%d] Anwesend", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 2, 	"[%d] Abwesend", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 3, 	"[%d] Sleeping", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 4, 	"[%d] Custom", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 5, 	"[%d] Custom", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 6, 	"[%d] Custom", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 7, 	"[%d] Custom", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 8, 	"[%d] Custom", "", -1);
			IPS_SetVariableProfileAssociation('SYR.AktivProfile', 9, 	"[%d] unknown", "", -1);
        }	

	}

	protected function RegisterVariables() {

		IPS_SetHidden($this->RegisterVariableInteger("requestCnt", "Request Cnt", "", 900), true);
		IPS_SetHidden($this->RegisterVariableInteger("receiveCnt", "Receive Cnt", "", 901), true);

		IPS_SetHidden($this->RegisterVariableBoolean("updateInProcess", "Update In Process", "", 905), true);
		IPS_SetHidden($this->RegisterVariableBoolean("skipAutoUpdate", "Skip Auto Update", "", 910), true);

		IPS_SetHidden($this->RegisterVariableInteger("updateSkipCnt", "Update Skip Cnt", "", 911), true);	
		IPS_SetHidden($this->RegisterVariableInteger("grantAdminRightsCnt", "Grant Admin Rights Cnt", "", 912), true);	
		IPS_SetHidden($this->RegisterVariableInteger("ErrorCnt", "Error Cnt", "", 920), true);
		IPS_SetHidden($this->RegisterVariableString("LastError", "Last Error", "", 921), true);
		IPS_SetHidden($this->RegisterVariableInteger("instanzInactivCnt", "Instanz Inactiv Cnt", "", 930), true);
		IPS_SetHidden($this->RegisterVariableString("processingTimeLog", "ProcessingTime Log", "", 935), true);
		IPS_SetHidden($this->RegisterVariableFloat("lastProcessingTotalDuration", "Last Processing Duration [ms]", "", 940), true);	
		IPS_SetHidden($this->RegisterVariableInteger("LastDataReceived", "Last Data Received", "~UnixTimestamp", 941), false);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'Alarm'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateAlarm", "Update 'Alarm'", $scriptScr, 990);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'Measurement'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateMeasurement", "Update 'Measurement'", $scriptScr, 991);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'Network'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateNetwork", "Update 'Network'", $scriptScr, 992);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'Profile'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateProfile", "Update 'Profile'", $scriptScr, 993);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'Settings'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateSettings", "Update 'Settings'", $scriptScr, 994);

		$scriptScr = sprintf("<?php STC_UpdateGroup(%s, 'ALL'); ?>",$this->InstanceID);
		$this->RegisterScript("UpdateALL", "Update 'ALL'", $scriptScr, 995);


		$scriptScr = sprintf("<?php STC_OpenShutoff(%s); ?>",$this->InstanceID);
		$this->RegisterScript("OpenShutoff", "Open Shutoff / Absperrung öffnen", $scriptScr, 998);

		$scriptScr = sprintf("<?php STC_CloseShutoff(%s); ?>",$this->InstanceID);
		$this->RegisterScript("CloseShutoff", "Close Shutoff / Absperrung schließen", $scriptScr, 998);


		$scriptScr = sprintf("<?php STC_SetAktivProfile(%s, 1); ?>",$this->InstanceID);
		$this->RegisterScript("SetProfile1", "Set Profile - 1 Anwesend", $scriptScr, 999);

		$scriptScr = sprintf("<?php STC_SetAktivProfile(%s, 2); ?>",$this->InstanceID);
		$this->RegisterScript("SetProfile2", "Set Profile - 2 Abwesend", $scriptScr, 999);

		$scriptScr = sprintf("<?php STC_SetAktivProfile(%s, 3); ?>",$this->InstanceID);
		$this->RegisterScript("SetProfile3", "Set Profile - 3 Sleeping", $scriptScr, 999);		

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