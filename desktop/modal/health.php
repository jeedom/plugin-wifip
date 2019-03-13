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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogics = wifip::byType('wifip');
?>

<table class="table table-condensed tablesorter" id="table_healthnetatmoThermostat">
	<thead>
		<tr>
			<th>{{Module}}</th>
			<th>{{ID}}</th>
			<th>{{Ip Fixe Lan}}</th>
			<th>{{Wifi}}</th>
			<th>{{Ip Fixe Wifi}}</th>
			<th>{{Tethering}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {
	if ($eqLogic->getConfiguration('ipfixenabled')) {
		$ipfix = '<td><span class="label label-success" style="font-size : 1em;">' . $eqLogic->getConfiguration('ipfix') . '</span></td>';
	} else {
		$ipfix = '<td><span class="label label-danger" style="font-size : 1em;">OFF</span></td>';
	}
	if ($eqLogic->getConfiguration('wifiEnabled')) {
		$wifi = '<td><span class="label label-success" style="font-size : 1em;">ON</span></td>';
	} else {
		$wifi = '<td><span class="label label-danger" style="font-size : 1em;">OFF</span></td>';
	}
	if ($eqLogic->getConfiguration('ipfixwifienabled')) {
		$ipfixwifi = '<td><span class="label label-success" style="font-size : 1em;">' . $eqLogic->getConfiguration('ipfixwifi') . '</span></td>';
	} else {
		$ipfixwifi = '<td><span class="label label-danger" style="font-size : 1em;">OFF</span></td>';
	}
	if ($eqLogic->getConfiguration('tetherenabled')) {
		$tether = '<td><span class="label label-success" style="font-size : 1em;">ON</span></td>';
	} else {
		$tether = '<td><span class="label label-danger" style="font-size : 1em;">OFF</span></td>';
	}
	echo '<tr><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em;">' . $eqLogic->getId() . '</span></td>';
	echo $ipfix;
	echo $wifi;
	echo $ipfixwifi;
	echo $tether;
}
?>
	</tbody>
</table>
