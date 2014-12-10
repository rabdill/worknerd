#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

query=$1
zip=$2

curl 'https://weworkremotely.com/categories/2/jobs' > listings.txt

#Strip out everything but the links to the job listings
cat listings.txt | grep "\<a href=\"/jobs/" > listings2.txt
mv listings2.txt listings.txt

#clean up the links, convert them to absolute URLs
sed -i 's|^[^"]*"\(/jobs/[0-9]*\)">|https://weworkremotely.com\1|' listings.txt
declare -a titles
declare -a urls
declare -a salaries
declare -a descriptions
declare -a companies

#Collect the job links
let i=0
while read line
do
	urls[$i]=`echo $line`
	echo "URL IS |${urls[$i]}|"
	i=$(( i+1 ))
done < listings.txt

#Hit up the job pages
total=${#urls[*]}
i=0
while [[ $i -lt $total ]]
do
	#check if you already have the listing
	listing_check=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
	SELECT COUNT(url) FROM listings WHERE url="${urls[$i]}";	
EOF`
	if [[ listing_check -gt 0 ]]; then
	    echo "Already have that listing."
	else
	    #read the job listing into a file
	    curl -L ${urls[$i]} > temp.txt

	    #scrape title
	    salaries[$i]=`cat temp.txt | grep baseSalary | sed 's/ *<span property="baseSalary" content="\([^"]*\)"><\/span>/\1/g'`

	#escape the mysql special characters (and HTML)
	urls[$i]=`echo ${urls[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
	titles[$i]=`echo ${titles[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
	salaries[$i]=`echo ${salaries[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt;/g' | sed 's/>/\&gt\;/g';`
	descriptions[$i]=`echo ${descriptions[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`

	SQL=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
	INSERT INTO unprocessed (url, company, title, salary, description) VALUES ('${urls[$i]}', '${companies[$i]}', '${titles[$i]}', '${salaries[$i]}', '${descriptions[$i]}')
EOF`
	fi	
	i=$(( i+1 ))
done