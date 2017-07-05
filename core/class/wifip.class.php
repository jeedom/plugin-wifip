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
			$wifip->wifiConnect();
			$wifip->tetherOn();
			$wifip->ethConnect();
			$changed = false;
			if ($wifip->getIsEnable() != 1) {continue;};
			log::add('wifip', 'debug', 'Pull Cron pour wifip');
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
			$wifisignal = str_replace('.', '', shell_exec("tail -n +3 /proc/net/wireless | awk '{ print $3 }'"));
			$wifiIp= shell_exec("ifconfig wlan0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
			$lanIp= shell_exec("ifconfig eth0 | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
			
		log::add('wifip','debug',$lanIp);
			$tetherIp= shell_exec("ifconfig tether | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
			$isconnectcmd = $wifip->getCmd(null, 'isconnect');
			if (is_object($isconnectcmd)) {
				$isconnectcmd_value = $isconnectcmd->execCmd();
				if ($isconnectcmd_value == null || $isconnectcmd_value != $isconnectcmd->formatValue($wifiup)) {
					$changed = true;
					$isconnectcmd->setCollectDate('');
					$isconnectcmd->event($wifiup);
				}
			}
			$isconnectethcmd = $wifip->getCmd(null, 'isconnecteth');
			if (is_object($isconnectethcmd)) {
				$isconnectethcmd_value = $isconnectethcmd->execCmd();
				if ($isconnectethcmd_value == null || $isconnectethcmd_value != $isconnectethcmd->formatValue($ethup)) {
					$changed = true;
					$isconnectethcmd->setCollectDate('');
					$isconnectethcmd->event($ethup);
				}
			}
			$signalcmd = $wifip->getCmd(null, 'signal');
			if (is_object($signalcmd)) {
				$signalcmd_value = $signalcmd->execCmd();
				if ($signalcmd_value == null || $signalcmd_value != $signalcmd->formatValue($wifisignal)) {
					$changed = true;
					$signalcmd->setCollectDate('');
					$signalcmd->event($wifisignal);
				}
			}
			if ($changed == true) {
				$wifip->refreshWidget();
			}
		}
	}
	
	public static function start() {
		log::add('wifip','debug',__('Jeedom started checking all connections',__FILE__));
		shell_exec('sudo connmanctl enable wifi');
		shell_exec('sudo connmanctl scan wifi');
		foreach (eqLogic::byType('wifip') as $wifip) {
			$wifip->wifiConnect();
			$wifip->ethConnect();
			$wifip->tetherOn();
       }
	}
	
	public static function listWifi($forced = false) {
		$eqLogic = eqLogic::byType('wifip');
					log::add('wifip','debug',$eqLogic[0]->getConfiguration('wifiEnabled'));
		$return =[];
		if ($eqLogic[0]->getConfiguration('wifiEnabled') == true || $forced === true){
			shell_exec('sudo connmanctl enable wifi');
			$scanresult = shell_exec(__('sudo connmanctl scan wifi',__FILE__));
			$services = shell_exec('sudo connmanctl services');
			$results = explode("\n", $services);
			$return = array();
			foreach ($results as $result) {
				if (strpos($result, 'wifi') !== false && strpos($result, 'hidden') === false) {
					$options = trim(substr($result,0,strpos($result,' ')));
					$result = substr($result, strpos($result,' '), strlen($result));
					$idwifi = trim(substr($result,strrpos($result,' '),strlen($result)));
					$ssid = trim(substr($result,0,strrpos($result,' ')));
					log::add('wifip','debug',$idwifi . '|' . $ssid . '|' . $options);
					if ($ssid != '' && !isset($return[$idwifi])) {
						$return[$idwifi] = $ssid;
					}
				}
			}
		}
		return $return;
	}
	
	public static function wifiConnect() {
		$eqLogic = eqLogic::byType('wifip');
		if ($eqLogic[0]->getConfiguration('wifiEnabled') === true){
			$idwifi =  $eqLogic[0]->getConfiguration('wifiSsid');
			$infowifi = explode('#|#',$idwifi);
			$keywifi = $eqLogic[0]->getConfiguration('wifiPassword');
			$confFile = "[" . $infowifi[0] . "]
Type = wifi
Name = " . $infowifi[1] . "
Passphrase = " . $keywifi . "
AutoConnect=true
";
			if (!is_dir('/tmp/' . $infowifi[0])) {
				mkdir('/tmp/' . $infowifi[0]);
			}
			file_put_contents('/tmp/' . $infowifi[0] . '/settings', $confFile );
			exec('sudo rm -rf /var/lib/connman/' . $infowifi[0] . '; sudo cp -R /tmp/' . $infowifi[0] . ' /var/lib/connman/;sudo chown -R root:root /var/lib/connman/' . $infowifi[0] . ';sudo chmod -R 644 /var/lib/connman/' . $infowifi[0]);
			if ($eqLogic[0]->getConfiguration('ipfixwifienabled') === true){
				$fixip = $eqLogic[0]->getConfiguration('ipfixwifi');
				$netmask = $eqLogic[0]->getConfiguration('netmaskwifi');
				$gateway = $eqLogic[0]->getConfiguration('gatewaywifi');
				shell_exec('sudo connmanctl config ' . $infowifi[0] . ' --ipv4 manual ' . $fixip . ' ' . $netmask . ' ' . $gateway);
				log::add('wifip','debug','sudo connmanctl config ' . $infowifi[0] . ' --ipv4 manual ' . $fixip . ' ' . $netmask . ' ' . $gateway);
			} else {
				shell_exec('sudo connmanctl config ' . $infowifi[0] . ' --ipv4 dhcp');
			}
			shell_exec("sudo connmanctl connect " . $infowifi[0]);
		} else {
			$eqLogic[0]->wifiDisConnect();
		}
	}
	
	public static function ethConnect() {
		$services = shell_exec('sudo connmanctl services');
		$results = explode("\n", $services);
		$return = array();
		$ideth = 'none';
		foreach ($results as $result) {
			if (strpos($result, 'Wired') !== false) {
				$ideth = trim(substr($result,strrpos($result,' '),strlen($result)));
			}
		}
		$eqLogic = eqLogic::byType('wifip');
		if ($eqLogic[0]->getConfiguration('ipfixenabled') == true && $ideth != 'none'){
			$fixip = $eqLogic[0]->getConfiguration('ipfix');
			$netmask = $eqLogic[0]->getConfiguration('netmask');
			$gateway = $eqLogic[0]->getConfiguration('gateway');
			shell_exec('sudo connmanctl config ' . $ideth . ' --ipv4 manual ' . $fixip . ' ' . $netmask . ' ' . $gateway);
			log::add('wifip','debug','sudo connmanctl config ' . $ideth . ' --ipv4 manual ' . $fixip . ' ' . $netmask . ' ' . $gateway);
		} else {
			shell_exec('sudo connmanctl config ' . $ideth . ' --ipv4 dhcp');
		}
	}
	
	public static function tetherOn() {
		$eqLogic = eqLogic::byType('wifip');
		$key = $eqLogic[0]->getConfiguration('tetherkey');
		if ($eqLogic[0]->getConfiguration('tetherenabled') == true){
			shell_exec('sudo connmanctl tether wifi on HotpointJeedom ' . $key);
		} else {
			shell_exec('sudo connmanctl tether wifi off');
		}		
	}
	
	public static function wifiDisConnect() {
		$eqLogic = eqLogic::byType('wifip');
		$idwifi =  $eqLogic[0]->getConfiguration('wifiSsid');
		$infowifi = explode('#|#',$idwifi);
		shell_exec("sudo connmanctl disconnect " . $infowifi[0]);
	}

	public static function getMac($_interface = 'eth0') {
		$interfaceIp= shell_exec("ifconfig $_interface | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
		$interfaceMask= shell_exec("ifconfig $_interface | grep -Eo 'Mask:?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'");
		$interfaceMac = shell_exec("ip addr show $_interface | grep -i 'link/ether' | grep -o -E '([[:xdigit:]]{1,2}:){5}[[:xdigit:]]{1,2}' | sed -n 1p");
		return [$interfaceMac,$interfaceIp,$interfaceMask];
	}
	
	public function preUpdate() {
		if ($this->getConfiguration('ipfixwifienabled')) {
			if ($this->getConfiguration('ipfixwifi') == '') {
				throw new Exception(__('L\'adresse IP fixe ne peut être vide', __FILE__));
			}
			if ($this->isValidIP($this->getConfiguration('ipfixwifi')) == false) {
				throw new Exception(__('L\'adresse IP fixe n\'est pas valide', __FILE__));
			}
			if ($this->getConfiguration('netmaskwifi') == '') {
				throw new Exception(__('Le netmask ne peut être vide', __FILE__));
			}
			if ($this->isValidNetmask($this->getConfiguration('netmaskwifi')) == false) {
				throw new Exception(__('Le netmask n\'est pas valide', __FILE__));
			}
			if ($this->getConfiguration('gatewaywifi') == '') {
				throw new Exception(__('La gateway ne peut être vide', __FILE__));
			}
			if ($this->isValidGateway($this->getConfiguration('ipfixwifi'),$this->getConfiguration('gatewaywifi')) == false) {
				throw new Exception(__('La gateway est invalide', __FILE__));
			}
		}
		
		if ($this->getConfiguration('tetherenabled')) {
			if (strlen($this->getConfiguration('tetherkey')) < 8 ) {
				throw new Exception(__('La clé tether doit faire au moins 8 caractères', __FILE__));
			}
		}
		
		if ($this->getConfiguration('ipfixenabled')) {
			if ($this->getConfiguration('ipfix') == '') {
				throw new Exception(__('L\'adresse IP fixe ne peut être vide', __FILE__));
			}
			if ($this->isValidIP($this->getConfiguration('ipfix')) == false) {
				throw new Exception(__('L\'adresse IP fixe n\'est pas valide', __FILE__));
			}
			if ($this->getConfiguration('netmask') == '') {
				throw new Exception(__('Le netmask ne peut être vide', __FILE__));
			}
			if ($this->isValidNetmask($this->getConfiguration('netmask')) == false) {
				throw new Exception(__('Le netmask n\'est pas valide', __FILE__));
			}
			if ($this->getConfiguration('gateway') == '') {
				throw new Exception(__('La gateway ne peut être vide', __FILE__));
			}
			if ($this->isValidGateway($this->getConfiguration('ipfix'),$this->getConfiguration('gateway')) == false) {
				throw new Exception(__('La gateway est invalide', __FILE__));
			}
		}
	}

	public function isValidIP($ipAddr) {
		$parts = explode('.',$ipAddr);
		if (count($parts) != 4)
			return false;
		foreach ($parts as $part) {
			if (!preg_match('/^[0-9]+$/',$part) || intval($part) > 255 || intval($part<0))
            return false;
		} 
		return true;
	}
	
	public function isValidNetmask($netmask) {
		$parts = explode('.',$netmask);
		if (count($parts) != 4)
			return false;
		foreach ($parts as $part) {
			if (!preg_match('/^[0-9]+$/',$part) || (intval($part) != 255 && intval($part !=0)))
            return false;
		} 
		return true;
	}
	
	public function isValidGateway($ipAddr,$gateway) {
		$parts = explode('.',$gateway);
		if (count($parts) != 4)
			return false;
		foreach ($parts as $part) {
			if (!preg_match('/^[0-9]+$/',$part) || intval($part) > 255 || intval($part<0))
            return false;
		} 
		$ipArray =  explode('.',$ipAddr);
		if ($ipArray[0] != $parts [0] || $ipArray[1] != $parts [1]) {
			return false;
		}
		return true;
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
			$disconnect->setName(__('Déconnecter Wifi', __FILE__));
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
		
		$isconnecteth = $this->getCmd(null, 'isconnecteth');
		if (!is_object($isconnecteth)) {
			$isconnecteth = new wifipCmd();
			$isconnecteth->setName(__('Etat Lan', __FILE__));
		}
		$isconnecteth->setEqLogic_id($this->getId());
		$isconnecteth->setLogicalId('isconnecteth');
		$isconnecteth->setType('info');
		$isconnecteth->setSubType('binary');
		$isconnecteth->save();
		
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
		$this->ethConnect();
		$this->tetherOn();
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
			case 'refresh':
				$eqLogic->cron5($eqLogic->getId());
				return;
				break;
			case 'connect':
				$eqLogic->setConfiguration('wifiEnabled', true);
				$eqLogic->save();
				$eqLogic->wifiConnect();
				$eqLogic->cron5($eqLogic->getId());
				break;
			case 'disconnect':
				$eqLogic->setConfiguration('wifiEnabled', false);
				$eqLogic->save();
				$eqLogic->wifiDisConnect();
				$eqLogic->cron5($eqLogic->getId());
				break;
		}
	}

	/************************Getteur Setteur****************************/
}
