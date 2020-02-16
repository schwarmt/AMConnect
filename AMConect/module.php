<?php
class AMConnect extends IPSModule {

    const STATUS_ACTIVE = 102;
    const STATUS_INACTIVE = 104;
    const STATUS_ERROR = 200;

    private $mappingAM = array(
     'Current' =>                        array("CURRENT", 1),
     'ChargingTime' =>                   array("CHARGING_TIME", 1),
     'ChargingCapacity' =>               array("CHARGING_CAPACITY", 1),
     'ChargingSearch' =>                 array("CHARGING_SEARCH", 1),
     'Status' =>                         array("STATUS", 0),
     'StatusText' =>                     array("STATUS_TEXT", 0),
     'Mode' =>                           array("MODE", 0),
     'BatteryTemperature' =>             array("BATTERY_TEMPERATURE", 0.01),
     'TimeSinceCharging' =>              array("TIME_SINCE_CHARGING", 1),
     'ChargingTemperature' =>            array("CHARGING_TEMPERATURE", 1),
     'TimeToNextMeasure' =>              array("TIME_TO_NEXT_MEASURE", 1),
     'ChargingNumber' =>                 array("CHARGING_NUMBER", 1),
     'MowingDuration' =>                 array("MOWING_DURATION", 1),
     'BatteryCapacity' =>                array("BATTERY_CAPACITY", 1),
     'EngineSpeed' =>                    array("ENGINE_SPEED", 1),
     'BatteryVoltage' =>                 array("BATTERY_VOLTAGE", 0.001),
     'BatteryVoltageCompensated' =>      array ("BATTERY_VOLTAGE_COMPENSATED", 0.001),
     'StatusSimulated' =>               array ("SIMULATED_STATUS", 0),
     'StatusGroup' =>                   array("STATUS_GROUP", 0),
     'LastUpdate' =>                    array("LAST_UPDATE", 0),
     'ModeText' =>                      array("MODE_TEXT",0),
     'CurrentArea' =>                   array("CURR_AREA",0 ),
     'PassageStatus' =>                 array("PASSAGE_STATUS", 0)
    );

    const modeMappingAM = array(
        1 => 'AUTO',
        3 => 'HOME',
        0 => 'MANUAL'
    );

    const statusGroupMappingAM = array(
        1 => 'MOW',
        2 => 'PARK',
        3 => 'TRANSFER',
        4 => 'ERROR',
        5 => 'UNKNOWN'
    );

    const currentAreaMappingAM = array(
        0 => 'AREA_UNDEFINED',
        1 => 'AREA_A',
        2 => 'AREA_B'
    );

    const passageStatusMappingAM = array(
        0 => 'PASSAGE_UNDEFINED',
        1 => 'PASSAGE_OPEN',
        2 => 'PASSAGE_CLOSED'
    );

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        //Properties
        $this->RegisterPropertyString('IP', "");
        $this->RegisterPropertyString('Port', "");
        $this->RegisterPropertyInteger('Period', 120);

        //Timer
        $this->RegisterTimer('UpdateData', 0, 'AMC_UpdateData($_IPS[\'TARGET\']);');

        // Variable Profiles
        //AMConnect.StatusGroup
        //AMConnect.CurrentArea
        //AMConnect.PassageStatus


        if (!IPS_VariableProfileExists('AMConnect.Mode')) {
            IPS_CreateVariableProfile('AMConnect.Mode', 1);
            IPS_SetVariableProfileValues('AMConnect.Mode', 0, 0, 0);
            IPS_SetVariableProfileAssociation('AMConnect.Mode', 0, 'MANUAL', '', 65280);
            IPS_SetVariableProfileAssociation('AMConnect.Mode', 1, 'AUTO', '', 16776960);
            IPS_SetVariableProfileAssociation('AMConnect.Mode', 3, 'HOME', '', 16744448);
        }

        if (!IPS_VariableProfileExists('AMConnect.StatusGroup')) {
            IPS_CreateVariableProfile('AMConnect.StatusGroup', 1);
            IPS_SetVariableProfileValues('AMConnect.StatusGroup', 0, 0, 0);
            IPS_SetVariableProfileAssociation('AMConnect.StatusGroup', 0, 'MANUAL', '', 65280);
            IPS_SetVariableProfileAssociation('AMConnect.StatusGroup', 1, 'AUTO', '', 16776960);
            IPS_SetVariableProfileAssociation('AMConnect.StatusGroup', 3, 'HOME', '', 16744448);
        }


        if (!IPS_VariableProfileExists('AMConnect.rpm')) {
            IPS_CreateVariableProfile("AMConnect.rpm", 1);
            IPS_SetVariableProfileText("AMConnect.rpm", "", " rpm");
            IPS_SetVariableProfileValues("AMConnect.rpm", 0, 0, 0);
            IPS_SetVariableProfileDigits("AMConnect.rpm", 0);
            IPS_SetVariableProfileIcon("AMConnect.rpm", "");
        }

        if (!IPS_VariableProfileExists('AMConnect.mAh')) {
            IPS_CreateVariableProfile("AMConnect.mAh", 1);
            IPS_SetVariableProfileText("AMConnect.mAh", "", " mAh");
            IPS_SetVariableProfileValues("AMConnect.mAh", 0, 0, 0);
            IPS_SetVariableProfileDigits("AMConnect.mAh", 0);
            IPS_SetVariableProfileIcon("AMConnect.mAh", "");
        }

        if (!IPS_VariableProfileExists('AMConnect.Minutes')) {
            IPS_CreateVariableProfile("AMConnect.Minutes", 1);
            IPS_SetVariableProfileText("AMConnect.Minutes", "", " min");
            IPS_SetVariableProfileValues("AMConnect.Minutes", 0, 0, 0);
            IPS_SetVariableProfileDigits("AMConnect.Minutes", 0);
            IPS_SetVariableProfileIcon("AMConnect.Minutes", "");
        }

        $this->RegisterVariableInteger('Current', $this->Translate('Current'), 'AMConnect.Minutes');
        $this->RegisterVariableInteger('ChargingTime', $this->Translate('ChargingTime'), 'AMConnect.Minutes');
        $this->RegisterVariableInteger('ChargingCapacity', $this->Translate('ChargingCapacity'), 'AMConnect.mAh');
        $this->RegisterVariableInteger('ChargingSearch', $this->Translate('ChargingSearch'), 'AMConnect.mAh');
        $this->RegisterVariableInteger('Status', $this->Translate('Status'));
        $this->RegisterVariableString('StatusText', $this->Translate('StatusText'));
        $this->RegisterVariableInteger('Mode', $this->Translate('Mode'), 'AMConnect.Mode');
        $this->RegisterVariableFloat('BatteryTemperature', $this->Translate('BatteryTemperature'), '~Temperature');
        $this->RegisterVariableInteger('TimeSinceCharging', $this->Translate('TimeSinceCharging'), 'AMConnect.Minutes');
        $this->RegisterVariableFloat('ChargingTemperature', $this->Translate('ChargingTemperature'), '~Temperature');
        $this->RegisterVariableInteger('TimeToNextMeasure', $this->Translate('TimeToNextMeasure'), 'AMConnect.Minutes');
        $this->RegisterVariableInteger('ChargingNumber', $this->Translate('ChargingNumber'));
        $this->RegisterVariableInteger('MowingDuration', $this->Translate('MowingDuration'), 'AMConnect.Minutes');
        $this->RegisterVariableInteger('BatteryCapacity', $this->Translate('BatteryCapacity'), 'AMConnect.mAh');
        $this->RegisterVariableInteger('EngineSpeed', $this->Translate('EngineSpeed'), 'AMConnect.rpm');
        $this->RegisterVariableFloat('BatteryVoltage', $this->Translate('BatteryVoltage'), '~Volt');
        $this->RegisterVariableFloat('BatteryVoltageCompensated', $this->Translate('BatteryVoltageCompensated'), '~Volt');
        $this->RegisterVariableInteger('StatusSimulated', $this->Translate('StatusSimulated'));
        $this->RegisterVariableString('StatusGroup', $this->Translate('StatusGroup'));
        $this->RegisterVariableBoolean('Active', $this->Translate('Active'), '~Switch');
        $this->RegisterVariableString('LastUpdate', $this->Translate('LastUpdate'));
        $this->RegisterVariableString('ModeText', $this->Translate('ModeText'));
        $this->RegisterVariableString('CurrentArea', $this->Translate('CurrentArea'));
        $this->RegisterVariableString('PassageStatus', $this->Translate('PassageStatus'));

        $this->EnableAction('Mode');
        $this->EnableAction('Active');
        $this->SetStatus(self::STATUS_INACTIVE);
    }



    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $this->validateConnectionAndStatus(boolval(GetValueBoolean($this->GetIDForIdent('Active'))));
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Mode':
                SetValue($this->GetIDForIdent($Ident), $Value);
                // TODO: Mode-Änderung
                break;
            case 'Active':
                $this->SetActive($Value);
                break;
            default:
                throw new Exception('Invalid Ident');
        }
    }

    public function SetActive(bool $Active)
    {
        SetValue($this->GetIDForIdent('Active'), $Active);
        $this->validateConnectionAndStatus($Active);
        return true;
    }

    public function CheckConnection()
    {
        $result = $this->validateConnectionAndStatus(true);
        if($result == true){
            SetValue($this->GetIDForIdent('Active'), true);
        }
    }

    private function validateConnectionAndStatus(bool $active)
    {
        $success=false;
        if (($this->ReadPropertyString('IP') != "") && ($this->ReadPropertyInteger('Period') > 0) && ($this->ReadPropertyString('Port') != "")) {
            if ($active) {
                $success = $this->UpdateData();
                if ($success == true) {
                    $this->SetStatus(self::STATUS_ACTIVE);
                    $this->SetTimerInterval('UpdateData', $this->ReadPropertyInteger('Period') * 1000);
                } else {
                    $this->SetStatus(self::STATUS_ERROR);
                    $this->SetTimerInterval('UpdateData', 0);
                }

            } else {
                $this->SetStatus(self::STATUS_INACTIVE);
                $this->SetTimerInterval('UpdateData', 0);
            }
        } else {
            // Parameter nicht vollständig
            $this->SetStatus(self::STATUS_INACTIVE);
            $this->SetTimerInterval('UpdateData', 0);
        }
        return $success;
    }

    public function SendCommand()
    {
        if($this->GetStatus() == self::STATUS_ACTIVE){
//            $ip = $this->ReadPropertyString('IP');
//            $url = "http://$ip/aircon/set_control_info";
//            $fanRatesRev = array(
//                0 => "A", 1 => "B", 2 => "3", 3 => "4", 4 => "5", 5 => "6", 6 => "7");
//            $data = array(
//                'pow' => strval(GetValueBoolean($this->GetIDForIdent('Power')) ? "1" : "0"),
//                'mode' => strval(GetValueInteger($this->GetIDForIdent('FanMode'))),
//                'stemp' => strval(GetValueFloat($this->GetIDForIdent('TargetTemperature'))),
//                'shum' => '0',
//                'f_rate' => strval($fanRatesRev[GetValueInteger($this->GetIDForIdent('FanRate'))]),
//                'f_dir' => strval(GetValueInteger($this->GetIDForIdent('FanDirection'))));
//            $options = array(
//                'http' => array(
//                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//                    'method'  => 'GET',
//                    'content' => http_build_query($data)
//                )
//            );
//            $content = http_build_query($data);
//            $context  = stream_context_create($options);
//            file_get_contents("$url?$content", false, $context);
        }
    }


    public function UpdateData()
    {
        $ip =$this->ReadPropertyString("IP");
        $port = $this->ReadPropertyString("Port");
        $content = Sys_GetURLContent("http://".$ip.":".$port."/api/amstatus");
        if($content == false){
            return false;
            // nicht erfolgreich
        }
        else {
            $json = json_decode($content);
            foreach ($this->mappingAM as $key => $value){
                $Variablen_ID = null;
                $Variablen_ID = $this->GetIDForIdent($key);
                if(isset($Variablen_ID)) {
                    $id=$value[0];
                    $factor=$value[1];
                    if(array_key_exists($id,$json)){
                        $am_value = $json->$id;
                        $Variable_Daten = IPS_GetVariable($Variablen_ID);
                        // 0 = Bool, 1 = Integer, 2 = Float, 3 = String
                        $Variablen_Typ = $Variable_Daten['VariableType'];
                        echo "id: ".$id." - value: ".$am_value." - Typ: ".$Variablen_Typ. " - Var-ID: ".$Variablen_ID;
                        switch ($Variablen_Typ) {
                            case 0:
                                SetValueBoolean($Variablen_ID, $am_value);
                                break;
                            case 1:
                                SetValueInteger($Variablen_ID, $am_value*$factor);
                                break;
                            case 2:
                                SetValueFloat($Variablen_ID, $am_value*$factor);
                                break;
                            case 3:
                                SetValueString($Variablen_ID, $am_value);
                                break;
                        }
                    }
                }
            }
        }
        return true;
     }
}