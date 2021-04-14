<?

// ConfigArrayOffset
if (!defined('ConfigArrOffset_Name'))
{
	define("ConfigArrOffset_Name", 0);
    define("ConfigArrOffset_UserLevel", 1);
	define("ConfigArrOffset_GroupId", 2);
    define("ConfigArrOffset_GroupName", 3);
	define("ConfigArrOffset_VarType", 4);
	define("ConfigArrOffset_Multiplikator", 5);
	define("ConfigArrOffset_VarPosition", 6);
    define("ConfigArrOffset_VarProfile", 7);
    define("ConfigArrOffset_VarEnabelAC", 8);
    define("ConfigArrOffset_Description", 9);
}

trait SafeTech_CommandSet {

    protected function GetCommandSetConfigArr() {

        $configArr = array();	

        $configArr["AB"]     = array("Absperrung",  							"USER",		100,    "Measurement",	VARIABLE::TYPE_INTEGER,	 1,		100,	"SYR.Absperrung",	        false,	"1 Opened | 2 Closed");
                    
        $configArr["PRF"]    = array("Aktiv Profile Nummer", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		100,	"",					        false,	"activ Profile Number");
        $configArr["PRN"]    = array("Number of Profiles", 						"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		110,	"",					        false,	"Number of profiles (available for user)");
                    
        $configArr["PA1"]    = array("Profile 1 available", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		111,	"",					        false,	"0 Profile not avialable | 1 Profile avialable");
        $configArr["PN1"]    = array("Profile 1 Name", 							"USER",		100,	"Profile",		VARIABLE::TYPE_STRING,	 1,		112,	"",					        false,	"max. 31 characters name");
        $configArr["PV1"]    = array("Profile 1 Volume Level", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		113,	"SYR.Profile.VolumeLevel",	false,	"0 Disabled | 1...9000l");
        $configArr["PT1"]    = array("Profile 1 Time Level", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		114,	"SYR.Profile.TimeLevel",	false,	"0 Disabled | 1...1500min (25h)");
        $configArr["PF1"]    = array("Profile 1 Max Flow", 						"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		115,	"SYR.Profile.MaxFlow",		false,	"0 Disabled | 1...5000l/h");
        $configArr["PM1"]    = array("Profile 1 Microleakage", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		116,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["PR1"]    = array("Profile 1 Return Time", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		117,	"SYR.Hour",					false,	"Profile x return time (to standard profile) 1-720h (30days)");
        $configArr["PB1"]    = array("Profile 1 Buzzer ON", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		118,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["PW1"]    = array("Profile 1 Leakage Warning ON",			"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		119,	"SYR.DisabledEnabled",	    false,	"0 Disabled | 1 Enabled");
                    
        $configArr["PA2"]    = array("Profile 2 available", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		121,	"",					        false,	"0 Profile not avialable | 1 Profile avialable");
        $configArr["PN2"]    = array("Profile 2 Name", 							"USER",		100,	"Profile",		VARIABLE::TYPE_STRING,	 1,		122,	"",					        false,	"max. 31 characters name");
        $configArr["PV2"]    = array("Profile 2 Volume Level", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		123,	"SYR.Profile.VolumeLevel",	false,	"0 Disabled | 1...9000l");
        $configArr["PT2"]    = array("Profile 2 Time Level", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		124,	"SYR.Profile.TimeLevel",	false,	"0 Disabled | 1...1500min (25h)");
        $configArr["PF2"]    = array("Profile 2 Max Flow", 						"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		125,	"SYR.Profile.MaxFlow",		false,	"0 Disabled | 1...5000l/h");
        $configArr["PM2"]    = array("Profile 2 Microleakage", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		126,	"SYR.DisabledEnabled",	    false,	"0 Disabled | 1 Enabled");
        $configArr["PR2"]    = array("Profile 2 Return Time", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		127,	"SYR.Hour",					false,	"Profile x return time (to standard profile) 1-720h (30days)");
        $configArr["PB2"]    = array("Profile 2 Buzzer ON", 					"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		128,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["PW2"]    = array("Profile 2 Leakage Warning ON",			"USER",		100,	"Profile",		VARIABLE::TYPE_INTEGER,	 1,		129,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
                    
        $configArr["TMP"]    = array("Leakage Protection Temporary Deactivation", "USER",   100,   "Settings",     VARIABLE::TYPE_INTEGER,  1,      90,    "SYR.TMP",                   false,  "0 temporary deactivation disabled | > 0 deactivated for n seconds");
                    
        $configArr["LNG"]    = array("Language",								"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		100,	"SYR.Language",				false,	"0 DE | 1 EN | 2 ES | 3 IT | 4 PL");
        $configArr["UNI"]    = array("Units",									"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		101,	"SYR.Units",				false,	"0 °C/bar/Liter | 1 °F/psi/US.liq.gal");
        $configArr["T2"]     = array("Max Flow Leakage Time",					"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		102,	"SYR.Minute",				false,	"0...99min");
        $configArr["BSA"]    = array("Floor Sensor",							"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		103,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["DMA"]    = array("Micro-Leakage-Test",						"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		104,	"SYR.DMA",					false,	"0 Disabled | 1 Warning | 2 Shutoff");
        $configArr["DRP"]    = array("Micro-Leakage-Test period",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		105,	"SYR.DRP",					false,	"0 Always | 1 Day | 2 Week | 3 Month");
        $configArr["BUZ"]    = array("Buzzer on alarm",							"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		106,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["CNL"]    = array("Conductivity Limit",						"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		107,	"SYR.CNL",					false,	"Maximum water conductivity value Conductivity alarm if exceeded 0 Disabled | 1...5000 uS/cm");
        $configArr["CNF"]    = array("Conductivity Factor",						"USER",		100,	"Settings",		VARIABLE::TYPE_FLOAT,	 0.1,	108,	"",					        false,	"Multiplier of conductivity value 0.5...5");
        $configArr["LWT"]    = array("Leakage warning threshold",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		109,	"SYR.Percent",				false,	"80...99 %");
        $configArr["VER"]    = array("Firmware Version",						"USER",		100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		110,	"",					        false,	"Safe-Tech (version number)");
        $configArr["SRN"]    = array("Serial Number",							"USER",		100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		111,	"",					        false,	"9 digits");
        $configArr["CNO"]    = array("Code Number",								"USER",		100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		112,	"",					        false,	"16 characters");
        $configArr["MAC"]    = array("MAC Address",								"USER",		100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		113,	"",				        	false,	"MAC address (WiFi interface)");
        $configArr["TYP"]    = array("Safe-Tec Type",							"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		114,	"",				        	false,	"0 return to default value | 1-255 (not changeable whith default settings)");
        $configArr["DKI"]    = array("Safe-Tec Device Kind ID",					"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		115,	"",					        false,	"0 return to default value | 1-255 (not changeable whith default settings)");
        $configArr["SRV"]    = array("Next maintenance",						"USER",		100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		116,	"",					        false,	"dd.mm.yyyy");
                    
        $configArr["BAT"]    = array("Battery voltage",							"USER",		110,	"Measurement",	VARIABLE::TYPE_FLOAT,	 1,		100,	"SYR.Voltage.2",	        false,	"Battery voltage in 1/100V, format x.xx");
        $configArr["NET"]    = array("DC voltage ",   							"USER",		110,	"Measurement",	VARIABLE::TYPE_FLOAT,	 1,		110,	"SYR.Voltage.2",	        false,	"DC voltage (power adaptor)");
        $configArr["CEL"]    = array("Temperature",   							"USER",		120,	"Measurement",	VARIABLE::TYPE_FLOAT,	 0.1,	120,	"SYR.Temp",		            false,	"0.0 ... 100.0°C (0 ... 1000)");
        $configArr["BAR"]    = array("Pressure",   								"USER",		120,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		130,	"SYR.mbar",			        false,	"in mbar, format x mbar");
        $configArr["CND"]    = array("Conductivity",   							"USER",		120,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		140,	"SYR.Conductivity",		    false,	"0...5000 uS/cm");
        $configArr["FLO"]    = array("Water flow",   							"USER",		130,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		150,	"SYR.WaterFlow",			false,	"0...6000 l/h");
        $configArr["LTV"]    = array("Last tapped volume",  					"USER",		130,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		160,	"SYR.Liter",		        false,	"Last tapped volume in liters");
                    
        $configArr["TSD"]    = array("Deactivate Temperature Sensor",			"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		200,	"SYR.ActiveDeactivated",	false,	"0 Active | 1 Deactivated (not changeable whith default settings)");
        $configArr["PSD"]    = array("Deactivate Pressure Sensor",  			"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		201,	"SYR.ActiveDeactivated",	false,	"0 Active | 1 Deactivated (not changeable whith default settings)");
        $configArr["CSD"]    = array("Deactivate Conductivity Sensor",			"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		202,	"SYR.ActiveDeactivated",	false,	"0 Active | 1 Deactivated (not changeable whith default settings)");
                    
        $configArr["WFC"]    = array("WiFi connect (SSID)",						"USER",		100,	"Network",		VARIABLE::TYPE_STRING,	 1,		100,	"",					        false,	"returns current saved SSID");
        $configArr["WFS"]    = array("WiFi State",								"USER",		100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		101,	"SYR.WFS",					false,	"0 Disconnected | 1 Connecting | 2 Connected");
        $configArr["WFR"]    = array("WiFi RSSI",								"USER",		100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		102,	"SYR.Percent",				false,	"1...100%");
        $configArr["WFL"]    = array("WiFi Scan",								"USER",		100,	"Network",		VARIABLE::TYPE_STRING,	 1,		103,	"",					        false,	"WiFi list in JSON format");
        $configArr["WIP"]    = array("IP Address",								"USER",		100,	"Network",		VARIABLE::TYPE_STRING,	 1,		104,	"",					        false,	"IP address obtained from DHCP in format w.x.y.z");
        $configArr["WGW"]    = array("Default Gateway",							"USER",		100,	"Network",		VARIABLE::TYPE_STRING,	 1,		105,	"",					        false,	"Def. gateway obtained from DHCP in format w.x.y.z");
        $configArr["WNS"]    = array("WiFi Disable Scan",						"SERVICE",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		106,	"",				        	false,	"0 scan for AP before connection | 1 scan disabled before connection");
        $configArr["WAH"]    = array("WiFi AP Hidden",							"SERVICE",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		107,	"",				        	false,	"0 AP not hidden (visible) | 1 AP hidden");
        $configArr["WAD"]    = array("WiFi AP Disabled",						"SERVICE",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		108,	"",				        	false,	"0 AP not disabled | 1 AP disabled");
        $configArr["APT"]    = array("WiFi AP Timeout",							"SERVICE",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		109,	"",				        	false,	"0 AP timeout not active | > 0 AP disabled after x seconds after internet connection");
        $configArr["DWL"]    = array("WiFi Deactivate",							"FACTORY",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		110,	"",				        	false,	"0 active (default) | 1 deactivated :: Deactivate WLAN interface (not changeable whith default settings)");
        $configArr["MQT"]    = array("MQTT Connection Type",					"USER",		100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		111,	"SYR.MQT",					false,	"0 no MQTT (HTTPS) | 1 MQTT");
        $configArr["MRT"]    = array("MQTT Reconnect Time",						"SERVICE",	100,	"Network",		VARIABLE::TYPE_INTEGER,	 1,		112,	"SYR.MRT",					false,	"0 disabled (default time 7h) 1...60min :: MQTT reconnect time if was disconnected");
                
        $configArr["ALA"]    = array("Ongoing Alarm",							"USER",		100,	"Alarm",		VARIABLE::TYPE_STRING,	 1,		300,	"",				        	false,	"Gets current alarm, FF- no alarm, A1...AB see below");
        $configArr["ALAi"]   = array("Ongoing Alarm",							"USER",		-99,	"Alarm",		VARIABLE::TYPE_INTEGER,	 1,		300,	"SYR.AlarmCodes",		   	false,	"Gets current alarm, FF- no alarm, A1...AB see below");
        $configArr["VLV"]    = array("Current Valve Status",					"SERVICE",	100,	"Alarm",		VARIABLE::TYPE_INTEGER,	 1,		300,	"SYR.VLV",					false,	"10 Closed | 11 Closing | 20 Open | 21 Opening | 30 Undefined");
        $configArr["ALM"]    = array("Alarm Memory",							"SERVICE",	100,	"Alarm",		VARIABLE::TYPE_STRING,	 1,		300,	"",				        	false,	"Get alarm memory (eight alarms) with '->' before current alarm, FF = empty");
                
        $configArr["VOL"]    = array("Cumulative Water Volume",					"SERVICE",	140,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		200,	"SYR.Liter",		        false,	"Vol[L]0...4294967295");
        $configArr["NPS"]    = array("Turbine no pulse time",					"SERVICE",	140,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		200,	"SYR.Seconds",				false,	"0 ... 4294967295 seconds");
        $configArr["VTO"]    = array("Valve test ongoing",						"USER",		140,	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		200,	"SYR.InactiveActive",		false,	"0 inactive | 1 active");
        $configArr["AVO"]    = array("Volume single water consumption", 		"FACTORY", 	130, 	"Measurement",	VARIABLE::TYPE_INTEGER,	 1,		200,	"SYR.Milliliter",		    false,	"Volume of the current consumption process, Resets after finish, in mililiters");
                
        $configArr["DBD"]    = array("MLT Pressure Drop",						"SERVICE",	100,	"Settings",		VARIABLE::TYPE_FLOAT,	 0.1,	310,	"SYR.Bar",					false,	"0.5 ... 3bar");
        $configArr["DBT"]    = array("MLT Pressure Drop Time",					"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		311,	"SYR.Seconds",				false,	"1 ... 15s");
        $configArr["DST"]    = array("MLT Test Time NOPULS",					"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		312,	"SYR.Minute",				false,	"1 ... 480min (8h)");
        $configArr["DCM"]    = array("MLT Test Time Close",						"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		313,	"SYR.Minute",				false,	"1 ... 30min");
        $configArr["DOM"]    = array("MLT Test Time Open",						"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		314,	"SYR.Seconds",				false,	"1 ... 60s");
        $configArr["DPL"]    = array("MLT pulses",								"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		315,	"",					        false,	"3 ... 99pulses");
        $configArr["DTC"]    = array("MLT verification cycles",					"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		316,	"",					        false,	"1 ... 20cycles");
                
        $configArr["SLP"]    = array("Self learning phase",						"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		320,	"SYR.SLP",					false,	"0 Disabled | 1...28days :: Self learning function");
        $configArr["SLO"]    = array("Self learning offset",					"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		321,	"",					        false,	"Self Learning values (volume, time) surplus in percentages");
        $configArr["SOF"]    = array("Self learning offset flow",				"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		322,	"",					        false,	"Self Learning max flow surplus in percentages");
        $configArr["SMF"]    = array("Self learning minimum flow",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		323,	"",					        false,	"Placeholder of the self Learning flow value");
        $configArr["SLE"]    = array("Self learning time to end",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		324,	"",					        false,	"Seconds remaining to the end of SLP");
        $configArr["SLV"]    = array("Self learning volume value",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		325,	"",					        false,	"Current volume value during SLP [l]");
        $configArr["SLT"]    = array("Self learning time value",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		326,	"",					        false,	"Current time value during SLP [s]");
        $configArr["SLF"]    = array("Self learning flow value",				"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		327,	"",				        	false,	"Current max. flow value during SLP [l/h]");
                
        $configArr["RTC"]    = array("System time",								"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"~UnixTimestamp",	    	false,	"Linux Epoch format, e.g. 1517389637");
        $configArr["IDS"]    = array("Daylight saving time",					"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"SYR.DisabledEnabled",		false,	"0 Disabled | 1 Enabled");
        $configArr["TMZ"]    = array("Time zone",								"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"",					        false,	"UTC-11 ... UTC+12  Every 15min");
        $configArr["TN"]     = array("Motor overrun",							"FACTORY",	100,	"Settings",		VARIABLE::TYPE_FLOAT,	 0.1,	400,	"SYR.Seconds",				false,	"Motor overrun in 1/10-second after a limit switch has been reached 0.0 ... 2.0s");
        $configArr["71"]     = array("LS deactivated",							"FACTORY",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"",					        false,	"Leakage protection deactivation 0 Nein | 1 Ja");
        $configArr["BUP"]    = array("Buzzer parameters",						"FACTORY",	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		400,	"",				        	false,	"Buzzer parameters: X = duration of sound [100ms] | Y = duration of repetition [100ms] | BUZ: Puls[100ms]:X Periode[100ms]:Y, setX Y");
        $configArr["ALD"]    = array("Alarm duration",							"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"SYR.ALD",					false,	"0 Unlimited | 1...3600s");
        $configArr["SRO"]    = array("Screen rotation",							"SERVICE",	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"SYR.SRO",					false,	"0 Standard | 90 Rotated 90° | 180 Rotated 180° | 270 Rotated 270°");
        $configArr["CLP"]    = array("Cluster profile",							"USER",		100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"SYR.CLP",					false,	"0 Shows profile name | 1 Shows „Cluster” instead of profile name");
        $configArr["BPB"]    = array("Enable profile changes via button", 		"USER", 	100,	"Settings",		VARIABLE::TYPE_INTEGER,	 1,		400,	"SYR.BPB",					false,	"0 Profile change blocked | 1 Profile change possible");
        $configArr["FSA"]    = array("Add (Pair) Floorsensor", 					"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		400,	"",					        false,	"Command adds/pairs Floorsensor with the given serial number Get command returns actual state of the pairing: 0 - not paired | 1 - pairing in progress (30s timeout) | 2 - paired OK");
        $configArr["FSL"]    = array("Paired Floorsensors list", 				"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		400,	"",					        false,	"Command returns paired Floorsensors list in JSON format");
                
        $configArr["ALH"]    = array("Alarm history file", 						"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		500,	"",					        false,	"Last 0 ... 100 entries in CSV format");
        $configArr["PAH"]    = array("Parameters history file", 				"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		501,	"",				        	false,	"Last 0 ... 100 entries in CSV format");
        $configArr["STH"]    = array("Statistics history file", 				"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		502,	"",				        	false,	"Last 0 ... 100 entries in CSV format");
                
        $configArr["SFV"]    = array("Firmware check", 							"USER", 	100,	"Settings",		VARIABLE::TYPE_STRING,	 1,		510,	"SYR.SFV",					false,	"0 – new firmware not available | 1 – new firmware available (Checked at startup and every 6h)");         
	
        return $configArr;

    }

}

?>