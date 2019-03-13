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
apt-get install -y firmware-ralink wpasupplicant wireless-tools
echo 60 > ${PROGRESS_FILE}
sudo connmanctl enable wifi
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
echo "########### Fin - Merci de redémarrer votre box afin d'activer le Wifi ##########"
rm ${PROGRESS_FILE}
