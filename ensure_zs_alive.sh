#!/bin/sh

## configuration ##

PUB_IP=127.0.0.1

ADMIN_MAIL=root@localhost
zsPID=$(pidof zoneserver)

kill -9 $zsPID

        echo "(${DT}) ${HOST}: zs stopped! starting it..." | mail "${ADMIN_MAIL}"
        cd $ZS_DIR
        ulimit -c unlimited
        ./SRO/zoneserver/zoneserver
        exit 3
fi

exit 0
