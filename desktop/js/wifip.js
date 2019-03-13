
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
$('#bt_healthwifip').on('click', function () {
    $('#md_modal').dialog({title: "{{Santé Wifip}}"});
    $('#md_modal').load('index.php?v=d&plugin=wifip&modal=health').dialog('open');
});
printWifiList();
printMacLan();
printMacWifi();
printTether();

if ($('.ipfixwifienabled').checked) {
	$('.ipfixewifi').css('display', 'block');
}

if ($('.ipfixenabled').checked) {
	$('.ipfixe').css('display', 'block');
}
 
 function printWifiList($forced=false){
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/wifip/core/ajax/wifip.ajax.php", // url du fichier php
            data: {
            	action: "listWifi",
				mode : $forced,
            },
            dataType: 'json',
			async: true,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
			success: function(data) {
			if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
            var options = '';
            for (i in data.result){
               options += '<option value="'+i+'#|#'+data.result[i]+'">'; 
				options += data.result[i]; 
				options += '</option>';  
            }
            $('.eqLogicAttr[data-l1key=configuration][data-l2key=wifiSsid]').empty().html(options);
        }
    });
}

function printMacLan(){
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/wifip/core/ajax/wifip.ajax.php", // url du fichier php
            data: {
            	action: "macfinder",
				interfa : "eth0",
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
			success: function(data) {
            if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
            $('.macLan').empty().append(data.result[0]);
            $('.ipLan').empty().append(data.result[1]);
            $('.maskLan').empty().append(data.result[2]);
        }
    });
}

function printTether(){
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/wifip/core/ajax/wifip.ajax.php", // url du fichier php
            data: {
            	action: "macfinder",
				interfa : "tether",
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
			success: function(data) {
            if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
            $('.iptether').empty().append(data.result[1]);
            $('.masktether').empty().append(data.result[2]);
        }
    });
}

function printMacWifi(){
	$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/wifip/core/ajax/wifip.ajax.php", // url du fichier php
            data: {
            	action: "macfinder",
				interfa : "wlan0",
            },
            dataType: 'json',
			async: true,
			global : false,
            error: function (request, status, error) {
            	handleAjaxError(request, status, error);
            },
			success: function(data) {
            if (data.state != 'ok') {
            	$('#div_alert').showAlert({message: data.result, level: 'danger'});
            	return;
            }
            $('.macWifi').empty().append(data.result[0]);
            $('.ipWifi').empty().append(data.result[1]);
            $('.maskWifi').empty().append(data.result[2]);
        }
    });
}

$('#bt_refreshWifiList').on('click',function(){
    printWifiList(true);
});

window.setInterval(function(){
	printMacLan();
	printMacWifi();
	printTether();
}, 5000);

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name"></td>';
     tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none;">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none;">';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}	