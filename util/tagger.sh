#! /bin/bash

source ../dbcredentials.sh

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT tech FROM techs
EOF`" > techs.txt

while read tech_name
do
    echo "Searching for |$tech_name|"
    techid=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT techid FROM techs WHERE tech="$tech_name";
EOF`
    echo "TechId is |$techid|"

    echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT jobid FROM listings WHERE description LIKE "%$tech_name%";
EOF`" > to-tag.txt

    #tag all the jobs:
    while read listingid
    do
        dupcheck=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT COUNT(tagid) FROM tags WHERE listingid=$listingid AND techid=$techid;
EOF` 

	if [[ dupcheck -eq 0 ]]; then
            echo "Tagging listingid |$listingid|"
            echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
INSERT INTO tags (listingid, techid) VALUES ('$listingid', '$techid')
EOF`"
        else
            echo "Already tagged."
        fi
    done < "to-tag.txt"
done < "techs.txt"	


