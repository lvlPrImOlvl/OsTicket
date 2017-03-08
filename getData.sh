#!/bin/sh
#Script que descarga desde un servidor remoto un archivo

#Descomentar si se desea separar en variables el comando
# host="192.168.100.9"
# serverUser="osticket"
# serverFile="prueba.csv"
# getPath="/home/$serverUser/$serverFile"
# destinationPath="/home/osticket/"
# scp $serverUser@$host:$getPath $destinationPath

/usr/bin/scp osticket@192.168.100.9:/home/osticket/prueba.csv /home/osticket/
