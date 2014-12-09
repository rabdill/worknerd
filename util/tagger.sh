#! /bin/bash

source ../dbcredentials.sh

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT tech FROM techs
EOF`" > techs.txt

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT jobid FROM unprocessed
EOF`" > unprocessed-ids.txt

while read tech_name
do
    echo "Searching for |$tech_name|"
    techid=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT techid FROM techs WHERE tech="$tech_name";
EOF`
    echo "TechId is |$techid|"

    echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT jobid FROM unprocessed WHERE description LIKE "%$tech_name%";
EOF`" > to-tag.txt

    #tag all the jobs:
    while read listingid
    do
        dupcheck=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT COUNT(tagid) FROM tags WHERE jobid="$listingid" AND techid="$techid";
EOF` 

	if [[ dupcheck -eq 0 ]]; then
            echo "Tagging listingid |$listingid|"
            echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
INSERT INTO tags (jobid, techid) VALUES ('$listingid', '$techid')
EOF`"
        else
            echo "Already tagged."
        fi
    done < "to-tag.txt"
done < "techs.txt"	

#move all the jobs out of "unprocessed"
while read to_move
do
    url=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    SELECT url FROM unprocessed WHERE jobid="$to_move"
EOF`
    company=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    SELECT company FROM unprocessed WHERE jobid="$to_move"
EOF`
    title=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    SELECT title FROM unprocessed WHERE jobid="$to_move"
EOF`
    salary=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    SELECT salary FROM unprocessed WHERE jobid="$to_move"
EOF`
    echo "Moving job from company $company"

    mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    INSERT INTO listings VALUES ('$to_move', '$url', '$company', '$title', '$salary');
    DELETE FROM unprocessed WHERE jobid='$to_move';
EOF
done < "unprocessed-ids.txt"
