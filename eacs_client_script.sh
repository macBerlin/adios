#!/bin/zsh
# ADIOS - REPORT macOS Wipe
# MICHAEL RIEDER 2021

# USERAGENT STRING MUST MATCH WITH THE SERVER CONFIG
#https://github.com/macBerlin/adios/blob/main/include/client_config.inc.php

USERAGENT="SomeRandomString#1"

WEBSERVERURL="https://macos.it-profs.de/scripts/wipe/adios.php"


UDID=$(ioreg -d2 -c IOPlatformExpertDevice | awk -F\" '/IOPlatformUUID/{print $(NF-1)}')
SomeRandomString#1

/usr/bin/curl -sk --user-agent "${USERAGENT}" ${WEBSERVERURL} -X POST -F "UDID=${UDID}" 
