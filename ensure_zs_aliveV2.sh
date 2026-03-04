#!/bin/bash

## vars ##
zsPID=$(pgrep zoneserver)
zonserver=/SRO/zoneserver/zoneserver

if [ -z "$zsPID" ]
then
    #ZS is currently not running we must start that bastert ASAP
    echo "ZS is not running"
    bash -c "cd /SRO/zoneserver && ./zoneserver"
    echo "ZS should be started now have fun :D"
else
    #ZS is running done why i just checked that WTF dude...
    echo "ZS is running on PID {$zsPID}"

fi
