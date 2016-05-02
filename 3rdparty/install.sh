#!/bin/bash
touch /tmp/dependancy_wifip_in_progress
echo 0 > /tmp/dependancy_wifip_in_progress
echo "########### Installation en cours ##########"
sudo connmanctl enable wifi
echo 100 > /tmp/dependancy_wifip_in_progress
echo "########### Fin ##########"
rm /tmp/dependancy_wifip_in_progress
