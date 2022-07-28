<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes**********************************/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class wifip extends eqLogic {
	/***************************Attributs*******************************/
	
	public static function cron5($_eqlogic_id = null) {
		if ($_eqlogic_id !== null) {
			$eqLogics = array(eqLogic::byId($_eqlogic_id));
		} else {
			$eqLogics = eqLogic::byType('wifip');
		}
		foreach ($eqLogics as $wifip) {
			log::add('wifip', 'debug', 'Pull Cron pour wifip');
			$wifip->wifiConnect();
			if ($wifip->getIsEnable() != 1) {continue;};
			if (!file_exists("/sys/class/net/eth0/operstate")) {
				$ethup = 0;
			} else {
				$ethup = (trim(file_get_contents("/sys/class/net/eth0/operstate")) == 'up') ? 1 : 0;
			}
			if (!file_exists("/sys/class/net/wlan0/operstate")) {
				$wifiup = 0;
			} else {
				$wifiup = (trim(file_get_contents("/sys/class/net/wlan0/operstate")) == 'up') ? 1 : 0;
			}
			$wifisignal = str_replace('.', '', shell_exec("sudo tail -n +3 /proc/net/wireless | awk '{ print $3 }'"));
			$wifiIp= shell_exec("sudo ifconfig wlan0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
			$lanIp= shell_exec("sudo ifconfig eth0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
			log::add('wifip','debug','Lan Ip is :' . $lanIp);
			log::add('wifip','debug','Wifi Ip is :' . $wifiIp);
			$wifip->checkAndUpdateCmd('isconnect', $wifiup);
			$wifip->checkAndUpdateCmd('isconnecteth', $ethup);
			$wifip->checkAndUpdateCmd('signal', $wifisignal);
			$wifip->checkAndUpdateCmd('lanip', $lanIp);
			$wifip->checkAndUpdateCmd('wifiip', $wifiIp);
			if ($wifip->getConfiguration('wifiEnabled',0) == 1){
				$wifip->checkAndUpdateCmd('ssid', $wifip->getConfiguration('wifiSsid',''));
			} else {
				$wifip->checkAndUpdateCmd('ssid', 'Aucun');
			}
		}
	}
	
	public static function start() {
		log::add('wifip','debug','Jeedom started checking all connections');
		foreach (eqLogic::byType('wifip') as $wifip) {
			$wifip->wifiConnect();
		}
	}
	
	public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = jeedom::getTmpFolder('wifip') . '/dependance';
		$return['state'] = 'ok';
		if (exec(system::getCmdSudo() . system::get('cmd_check') . '-E "wpasupplicant|wireless\-tools|network\-manager|resolvconf" | wc -l') < 4) {
			$return['state'] = 'nok';
		}
		return $return;
	}
	
	public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('wifip') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}
	
	public static function isWificonnected ($ssid) {
		$result = shell_exec("sudo nmcli d | grep '" . $ssid . "'");
		log::add('wifip','debug',$result);
		if (strpos($result,'connected') === false && strpos($result,'connecté') === false){
			return false;
		}
		return true;
	}
  
  	public static function isWifiProfileexist($ssid) {
		$result = shell_exec("nmcli --fields NAME con show");
		$countProfile = substr_count($result, $ssid);
      	if ($countProfile > 1){
        	log::add('wifip','debug','suppression des profils');
        	//shell_exec("nmcli --pretty --fields UUID,TYPE con show | grep wifi | awk '{print $1}' | while read line; do nmcli con delete uuid  $line; done");
        	return true;
        }else if ($countProfile == 1){
        	return true;
        }else{
        	return false;
        }
	}
	
	public static function listWifi($forced = false) {
		$eqLogic = eqLogic::byType('wifip');
		log::add('wifip','debug','Wifi enabled : ' . $eqLogic[0]->getConfiguration('wifiEnabled'));
		$return =[];
		if ($eqLogic[0]->getConfiguration('wifiEnabled') == true || $forced == true){
			$scanresult = shell_exec('sudo nmcli -f SSID,SIGNAL,SECURITY,CHAN -t -m tabular dev wifi list');
			$results = explode("\n", $scanresult);
			$return = array();
			foreach ($results as $result) {
				log::add('wifip','debug',$result);
				$result = str_replace('\:','$%$%',$result);
				$wifiDetail = explode(':',$result);
				$chan = $wifiDetail[3];
				$security = $wifiDetail[2];
				if ($security == ''){
					$security = 'Aucune';
				}
				$signal =  $wifiDetail[1];
				$ssid = str_replace('$%$%','\:',$wifiDetail[0]);
				if ($ssid != '') {
					log::add('wifip','debug',$ssid . ' with signal ' . $signal . ' and security ' . $security . ' on channel ' . $chan);
					if (isset($return[$ssid]) && $return[$ssid]['signal']> $signal){
						continue;
					}
					$return[$ssid] = array('ssid' => $ssid,'signal'=>$signal,'security'=>$security,'channel'=>$chan);
				}
			}
		}
		return $return;
	}

	public static function getMac($_interface = 'eth0') {
		$interfaceIp= shell_exec("sudo ifconfig $_interface | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
		$interfaceMac = shell_exec("sudo ip addr show $_interface | grep -i 'link/ether' | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed -n 1p");
		return [$interfaceMac,$interfaceIp];
	}
	
	public function wifiConnect() {
		if ($this->getConfiguration('wifiEnabled') == true){
			$ssid = $this->getConfiguration('wifiSsid','');
			if (self::isWificonnected($ssid) === false) {
				log::add('wifip','debug','Not Connected to ' . $ssid . '. Connecting ...');
				shell_exec("sudo ip link set wlan0");
              	if(self::isWifiProfileexist($ssid) === true) {
                	$exec = "sudo nmcli con up '".$ssid."'";
                }else{
                	$password = $this->getConfiguration('wifiPassword','');
                    if ($password != ''){
                        $exec = "sudo nmcli dev wifi connect '" . $ssid . "' password '" . $password . "'";
                    } else {
                    $exec ="sudo nmcli dev wifi connect '" . $ssid . "'";
                    }
                }
				log::add('wifip','debug','Executing ' . $exec);
				shell_exec($exec);
			}
		} else {
			log::add('wifip','debug','Executing sudo nmcli dev disconnect wlan0');
			shell_exec('sudo nmcli dev disconnect wlan0');
		}
	}

	public function postSave() {
		$connect = $this->getCmd(null, 'connect');
		if (!is_object($connect)) {
			$connect = new wifipCmd();
			$connect->setLogicalId('connect');
			$connect->setIsVisible(1);
			$connect->setName(__('Connecter Wifi', __FILE__));
		}
		$connect->setType('action');
		$connect->setSubType('other');
		$connect->setEqLogic_id($this->getId());
		$connect->save();
		
		$disconnect = $this->getCmd(null, 'disconnect');
		if (!is_object($disconnect)) {
			$disconnect = new wifipCmd();
			$disconnect->setLogicalId('disconnect');
			$disconnect->setIsVisible(1);
			$disconnect->setName(__('D?connecter Wifi', __FILE__));
		}
		$disconnect->setType('action');
		$disconnect->setSubType('other');
		$disconnect->setEqLogic_id($this->getId());
		$disconnect->save();
		
		$isconnect = $this->getCmd(null, 'isconnect');
		if (!is_object($isconnect)) {
			$isconnect = new wifipCmd();
			$isconnect->setName(__('Etat Wifi', __FILE__));
		}
		$isconnect->setEqLogic_id($this->getId());
		$isconnect->setLogicalId('isconnect');
		$isconnect->setType('info');
		$isconnect->setSubType('binary');
		$isconnect->save();
		
		$signal = $this->getCmd(null, 'signal');
		if (!is_object($signal)) {
			$signal = new wifipCmd();
			$signal->setName(__('Signal', __FILE__));
		}
		$signal->setEqLogic_id($this->getId());
		$signal->setLogicalId('signal');
		$signal->setType('info');
		$signal->setSubType('numeric');
		$signal->save();
		
		$lanip = $this->getCmd(null, 'lanip');
		if (!is_object($lanip)) {
			$lanip = new wifipCmd();
			$lanip->setName(__('Lan IP', __FILE__));
		}
		$lanip->setEqLogic_id($this->getId());
		$lanip->setLogicalId('lanip');
		$lanip->setType('info');
		$lanip->setSubType('string');
		$lanip->save();
		
		$wifiip = $this->getCmd(null, 'wifiip');
		if (!is_object($wifiip)) {
			$wifiip = new wifipCmd();
			$wifiip->setName(__('Wifi IP', __FILE__));
		}
		$wifiip->setEqLogic_id($this->getId());
		$wifiip->setLogicalId('wifiip');
		$wifiip->setType('info');
		$wifiip->setSubType('string');
		$wifiip->save();
		
		$ssid = $this->getCmd(null, 'ssid');
		if (!is_object($ssid)) {
			$ssid = new wifipCmd();
			$ssid->setName(__('SSID', __FILE__));
		}
		$ssid->setEqLogic_id($this->getId());
		$ssid->setLogicalId('ssid');
		$ssid->setType('info');
		$ssid->setSubType('string');
		$ssid->save();
		
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new wifipCmd();
		}
		$refresh->setName(__('Rafraichir', __FILE__));
		$refresh->setLogicalId('refresh');
		$refresh->setEqLogic_id($this->getId());
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->save();
	}

	public function postAjax() {
		$this->wifiConnect();
	}
}

class wifipCmd extends cmd {
	/***************************Attributs*******************************/


	/*************************Methode static****************************/


	/***********************Methode d'instance**************************/

	public function execute($_options = null) {
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$action = $this->getLogicalId();
		switch ($action) {
			case 'connect':
				$eqLogic->setConfiguration('wifiEnabled', true);
				$eqLogic->save();
				break;
			case 'disconnect':
				$eqLogic->setConfiguration('wifiEnabled', false);
				$eqLogic->save();
				break;
			 case 'repair':
				$ssidConf = $eqLogic->getConfiguration('wifiSsid');
            			if($ssidConf == ""){
					$eqLogic->setConfiguration('wifiSsid', shell_exec('iwgetid -r'));
					$eqLogic->save();
					message::add('wifip', 'sauvegarde ssid');
				}
				$connFile = shell_exec('nmcli --fields TYPE,FILENAME con show --active | grep -i wifi | cut -c46-600');
				message::add('wifip', 'suppression des profils pour'.$connFile);
				shell_exec('sudo find /etc/NetworkManager/system-connections -type f ! -name "'.$connFile.'" -delete');
				shell_exec("sudo rm -f /var/log/daemon.log*");
				message::add('wifip', 'suppression OK merci de redémarrer');
				break;
		}
		$eqLogic->cron5($eqLogic->getId());
	}

	/************************Getteur Setteur****************************/
}
?>
