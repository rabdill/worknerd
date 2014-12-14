#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

qty=`mysql -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT COUNT(jobid) FROM unprocessed;
EOF`

echo "Starting with $qty listings."

bad=`mysql -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT COUNT(jobid) FROM unprocessed WHERE description="" OR company="";
EOF`

echo "Deleting $bad listings because of missing data."

mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
DELETE FROM unprocessed WHERE description="" OR company="";
EOF

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT tech FROM techs
EOF`" > techs.txt

echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT jobid FROM unprocessed
EOF`" > unprocessed-ids.txt

while read tech_name
do
    #escape special characters
    tech_name=`echo $tech_name | sed 's/\+/\\\+/g'`

    echo "Searching for |$tech_name|"
    techid=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT techid FROM techs WHERE tech="$tech_name";
EOF`
    echo "TechId is |$techid|"

    echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
SELECT jobid FROM unprocessed WHERE description REGEXP "[^a-zA-Z]$tech_name[^a-zA-Z]";
EOF`" > to-tag.txt #mysql regex is case-insensitive out of the box

    #tag all the jobs:
    while read listingid
    do
        echo "Tagging listingid |$listingid|"
        echo "`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
	INSERT INTO tags (jobid, techid) VALUES ('$listingid', '$techid')
EOF`"
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

    mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
    INSERT INTO listings VALUES ('$to_move', '$url', '$company', '$title', '$salary');
    DELETE FROM unprocessed WHERE jobid='$to_move';
EOF
done < "unprocessed-ids.txt"
