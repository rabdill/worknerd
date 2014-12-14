#! /bin/bash

max_per_tech=$1

if [[ -z $max_per_tech ]]; then
    echo "WARN: No maximum searches per tech provided. Defaulting to 50."
    max_per_tech=50
fi

source /var/www/html/worknerd/dbcredentials.sh

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT tech FROM techs
EOF`" > techs.txt

while read tech_name
do
    /var/www/html/worknerd/spider/./sologig.sh $tech_name $max_per_tech
    /var/www/html/worknerd/spider/./dice.sh $tech_name $max_per_tech
done < "techs.txt"	

/var/www/html/worknerd/util/./tagger.sh
