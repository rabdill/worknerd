#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
UPDATE listings SET salary=null WHERE salary REGEXP '^[^0-9]*$'
EOF
