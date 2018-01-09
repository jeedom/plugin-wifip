#!/bin/bash
touch /tmp/dependancy_wifip_in_progress
echo 0 > /tmp/dependancy_wifip_in_progress
echo "########### Installation en cours ##########"
sudo apt-get install -y firmware-ralink wpasupplicant wireless-tools
echo 50 > /tmp/dependancy_wifip_in_progress
sudo connmanctl enable wifi
echo 100 > /tmp/dependancy_wifip_in_progress
echo "########### Fin - Merci de redémarrer votre box afin d'activer le Wifi ##########"
rm /tmp/dependancy_wifip_in_progress
