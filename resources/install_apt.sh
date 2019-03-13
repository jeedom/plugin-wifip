PROGRESS_FILE=/tmp/dependancy_wifip_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
apt-get update
echo 50 > ${PROGRESS_FILE}
sudo apt-get install -y firmware-ralink wpasupplicant wireless-tools
echo 60 > ${PROGRESS_FILE}
sudo apt install -y network-manager
sudo ip link set wlan0 down
sudo ip link set wlan0 up
sudo apt-get install resolvconf
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
echo "########### Fin - Merci de redémarrer votre box dans 3 minutes afin d'activer le Wifi ##########"
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
echo "########### Fin - Merci de redémarrer votre box dans 3 minutes afin d'activer le Wifi ##########"
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
echo "########### Fin - Merci de redémarrer votre box dans 3 minutes afin d'activer le Wifi ##########"
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
echo "########### Fin - Merci de redémarrer votre box dans 3 minutes afin d'activer le Wifi ##########"
rm ${PROGRESS_FILE}
sudo service resolvconf restart
sudo systemd-resolve --status
sudo apt remove -y connman

