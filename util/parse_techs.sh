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
    echo "****************************************************"
    echo "*****       Now searching for $tech_name       *****"
    echo "****************************************************"
    /var/www/html/worknerd/spider/./sologig.sh $tech_name $max_per_tech
    
    #because the dice spider pulls in URLs in pages of 50, it will
    #stop after the page in which your limit is hit.
    #for example: a limit of 55 yields 100 results.
    /var/www/html/worknerd/spider/./dice.sh $tech_name $max_per_tech
done < "techs.txt"	

/var/www/html/worknerd/util/./tagger.sh
