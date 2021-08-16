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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function wifip_install() {
	$eqLogic = wifip::byLogicalId('wifip', 'wifip');
	if (!is_object($eqLogic)) {
		$eqLogic = new wifip();
		$eqLogic->setLogicalId('wifip');
		$eqLogic->setCategory('multimedia', 1);
		$eqLogic->setName('Wifip');
		$eqLogic->setEqType_name('wifip');
		$eqLogic->setIsVisible(1);
		$eqLogic->setIsEnable(1);
		$eqLogic->save();
	}
    foreach (eqLogic::byType('wifip') as $wifip) {
        $wifip->save();
    }
}

function wifip_update() {
	$eqLogic = wifip::byLogicalId('wifip', 'wifip');
	if (!is_object($eqLogic)) {
		$eqLogic = new wifip();
		$eqLogic->setLogicalId('wifip');
		$eqLogic->setCategory('multimedia', 1);
		$eqLogic->setName('Wifip');
		$eqLogic->setEqType_name('wifip');
		$eqLogic->setIsVisible(1);
		$eqLogic->setIsEnable(1);
		$eqLogic->save();
	}
    foreach (eqLogic::byType('wifip') as $wifip) {
		$wifip->save();
    }
	//shell_exec('sudo find /etc/NetworkManager/system-connections/ -name "Orange*" -exec  sudo rm {} \;');
	shell_exec("nmcli --pretty --fields UUID,TYPE con show | grep wifi | awk '{print $1}' | while read line; do nmcli con delete uuid  $line; done");
	shell_exec("sudo rm -f /var/log/daemon.log*");	
}

?>
