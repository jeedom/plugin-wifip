<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

sendVarToJS('eqType', 'wifip');
$eqLogics = eqLogic::byType('wifip');
?>
<div class="row row-overflow">
    <div class="col-lg-2">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
                foreach ($eqLogics as $eqLogic) {
                    echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
   <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
   <div class="cursor" id="bt_healthwifip" style="text-align: center; background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
      <i class="fa fa-medkit" style="font-size : 5em;color:#767676;"></i>
    <br>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676">{{Santé}}</span>
  </div>
</div>
        <legend><i class="fa fa-wifi"></i>  {{Mon Wifip}}
        </legend>
         <?php
                foreach ($eqLogics as $eqLogic) {
                    $opacity = '';
                    if ($eqLogic->getIsEnable() != 1) {
                        $opacity = 'opacity:0.3;';
                    }
                    echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="text-align: center; background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
                    echo '<img src="plugins/wifip/doc/images/wifip_icon.png" height="105" width="95" />';
                    echo "<br>";
                    echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;">' . $eqLogic->getHumanName(true, true) . '</span>';
                    echo '</div>';
                }
                ?>
            </div>  

    <div class="col-lg-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
	<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
  <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
  <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab"><br/>
      <div class="row">
	<div class="col-sm-7">
       <form class="form-horizontal">
            <fieldset>
                <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label">{{Nom de l'équipement}}</label>
                    <div class="col-lg-4">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                    </div>
					
                </div>
                <div class="form-group">
                <label class="col-lg-3 control-label" >{{Objet parent}}</label>
                    <div class="col-lg-4">
                        <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                            <option value="">{{Aucun}}</option>
                            <?php
                            foreach (object::all() as $object) {
                                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                 <div class="form-group">
                <label class="col-sm-3 control-label"></label>
                <div class="col-sm-9">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                </div>
              </div>
				<legend><i class="fa fa-wifi"></i>  {{Wifi}}</legend>
                <div class="form-group">
				<div class="col-lg-3">
				</div>
					<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr ipfixwifienabled" data-l1key="configuration" data-l2key="wifiEnabled" onchange="if(this.checked == true){$('.wifi').css('display', 'block');$('.tetherenabled').prop('checked', true);} else {$('.wifi').css('display', 'none');}" unchecked/>{{Activer le wifi}}</label>
            
				</div>
                <div class="form-group wifi" style="display:none">
                    <label class="col-lg-3 control-label">{{Réseau wifi}}</label>
                    <div class="col-lg-4">
                        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="wifiSsid" ></select>
                    </div>
					<div class="col-lg-2">
                <a class="btn btn-info" id="bt_refreshWifiList"><i class="fa fa-refresh"></i></a>
            </div>
                </div>
                <div class="form-group wifi" style="display:none">
                    <label class="col-lg-3 control-label">{{Clef}}</label>
                    <div class="col-lg-4">
                         <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="wifiPassword" />
                    </div>
                </div>
				<div class="expertModeVisible">
				<div class="form-group">
				<div class="col-lg-3">
				</div>
				 <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr ipfixwifienabled" data-l1key="configuration" data-l2key="ipfixwifienabled" onchange="if(this.checked == true){$('.ipfixewifi').css('display', 'block');} else {$('.ipfixewifi').css('display', 'none');}" unchecked/>{{Ip Fixe Wifi}}</label>
                </div>
				<div class="form-group ipfixewifi" style="display : none;">
					<div class="alert alert-danger col-lg-12">
						{{Attention le réglage d'une ip fixe peut rendre votre box invisible du réseau. Vérifiez bien les infos renseignées !! }}
					</div>
                </div>
				<div class="form-group ipfixewifi" style="display : none;">
                    <label class="col-lg-3 control-label">{{IP fixe}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ipfixwifi" />
                    </div>
                </div>
				<div class="form-group ipfixewifi" style="display : none;">
                    <label class="col-lg-3 control-label">{{Netmask}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="netmaskwifi"  />
                    </div>
                </div>
				<div class="form-group ipfixewifi" style="display : none;">
                    <label class="col-lg-3 control-label">{{Gateway}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="gatewaywifi"  />
                    </div>
                </div>
				</div>
				<div class="expertModeVisible">
				<legend><i class="fa fa-rss"></i>  {{Tethering}}</legend>
				<div class="form-group">
				<div class="col-lg-3">
				</div>
				<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr ipfixwifienabled" data-l1key="configuration" data-l2key="tetherenabled" onchange="if(this.checked == true){$('.wifion').prop('checked', true);$('.ipfixwifienabled').prop('checked', true);$('.tetheron').css('display', 'block');} else {$('.tetheron').css('display', 'none');}" unchecked/>{{Activer Tether}}</label>
				</div>
				<div class="form-group tetheron" style="display : none;">
					<div class="alert alert-danger col-lg-12">
						{{Attention en activant le tether vous ne serez plus connecté au wifi. La box sera accessible en vous connectant au HotpointJeedom avec la clé et en vous rendant à l'adresse tether indiqué à droite! }}
					</div>
                </div>
				<div class="form-group tetheron" style="display : none;">
                    <label class="col-lg-3 control-label">{{Clé (8 caractères minimum)}}</label>
                    <div class="col-lg-4">
                         <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tetherkey"  />
                    </div>
                </div>
				</div>
				<div class="expertModeVisible">
				<legend><i class="fa fa-wrench"></i>  {{Ethernet}}</legend>
				<div class="form-group">
				<div class="col-lg-3">
				</div>
				<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr ipfixwifienabled" data-l1key="configuration" data-l2key="ipfixenabled" onchange="if(this.checked == true){$('.ipfixe').css('display', 'block');} else {$('.ipfixe').css('display', 'none');}" unchecked/>{{Activer ip fixe}}</label>
				</div>
				<div class="form-group ipfixe" style="display:none">
					<div class="alert alert-danger col-lg-12">
						{{Attention le réglage d'une ip fixe peut rendre votre box invisible du réseau. Vérifiez bien les infos renseignées !! }}
					</div>
                </div>
				<div class="form-group ipfixe" style="display:none">
                    <label class="col-lg-3 control-label">{{IP fixe}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ipfix" />
                    </div>
                </div>
				<div class="form-group ipfixe" style="display:none">
                    <label class="col-lg-3 control-label">{{Netmask}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="netmask"  />
                    </div>
                </div>
				<div class="form-group ipfixe" style="display:none">
                    <label class="col-lg-3 control-label">{{Gateway}}</label>
                    <div class="col-lg-4">
                         <input type="input" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="gateway"  />
                    </div>
                </div>
				</div>
				
            </fieldset>
        </form>
		</div>
<div class="col-sm-5">
  <form class="form-horizontal">
    <fieldset>
      <legend><i class="fa fa-info-circle"></i>  {{Informations}}</legend>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse MAC ethernet}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info macLan" style="font-size:1em;cursor:default;"></span>
                    </div>
				</div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse Ip ethernet}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info ipLan" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Masque ethernet}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info maskLan" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse MAC wifi}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info macWifi" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse Ip wifi}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info ipWifi" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Masque wifi}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info maskWifi" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Adresse Ip Tether}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info iptether" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
				<div class="form-group">
                    <label class="col-lg-4 control-label">{{Masque Tether}}</label>
                    <div class="col-lg-4">
                        <span class="label label-info masktether" style="font-size:1em;cursor:default;"></span>
                    </div>
                </div>
</fieldset>
</form>
</div>
</div>
</div>
<div role="tabpanel" class="tab-pane" id="commandtab">
       <table id="table_cmd" class="table table-bordered table-condensed">
             <thead>
                <tr>
                    <th>{{Nom}}</th><th>{{Options}}</th><th>{{Action}}</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>
</div>
</div>
</div>

<?php include_file('desktop', 'wifip', 'js', 'wifip'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
