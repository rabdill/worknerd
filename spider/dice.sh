#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

query=$1
max=$2
zip=$3

if [[ -z $max ]]; then
    echo "WARN: No maximum crawl number provided. Defaulting to 500."
    max=500
fi


#Count how many results there are
results=`curl -G -v http://www.dice.com/job/results/${zip} -d caller=basic -d q=${query} -d x=all -d p=z -d n=50 | grep "Search results" | sed 's/^.*of \([0-9]\+\)<\/h2>/\1/'`

echo "$results RESULTS TO PARSE"

if [[ $results -gt $max ]]; then
    echo "WARN: Potential results exceeds max crawl number. Reducing target down to ${max}."
    results=$max
fi

offset=0

while [[ $offset -lt $results ]]
do
    echo "OFFSET IS |$offset|. RESULTS IS |$results|."
    curl -G 'http://www.dice.com/job/results/${zip}' -d caller=basic -d q=${query} -d x=all -d p=z -d n=50 -d o=$offset  > listings.txt
    #Strip out everything but the links to the job listings
    cat listings.txt | grep "\<a href=\"/job/result/" > listings2.txt
    mv listings2.txt listings.txt

    #strip out the leading spaces and stuff
    sed -i 's/^.*<div>//' listings.txt

    #clean up the links, convert them to absolute URLs
    sed -i 's|/job/result|http://www.dice.com/job/result|' listings.txt
    declare -a titles
    declare -a urls
    declare -a salaries
    declare -a descriptions
    declare -a companies

#Collect the job links
    let i=0
    while read line
    do
        #grab the URL, strip out any parameters
	urls[$i]=`echo $line | sed 's/<a href="\([^\?]*\).*">[^<]*<\/a>/\\1/g'`
	echo "URL IS |${urls[$i]}|"
	#grab the title
	titles[$i]=`echo $line | sed 's/<a href[^>]*>\(.*\)<\/a>/\\1/g'`
	echo "TITLE IS |${titles[$i]}|";
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

	    #scrape out the salary section
	    salaries[$i]=`cat temp.txt | grep baseSalary | sed 's/ *<span property="baseSalary" content="\([^"]*\)"><\/span>/\1/g'`
	    companies[$i]=`cat temp.txt | grep 'companyName =' | sed 's/\s*companyName = "\([^"]*\)";.*$/\1/'` 
	    #figure out which format is on the page, scrape it
	    if [[ `cat temp.txt | grep -c 'div id=\"detailDescription\"'` -gt 0 ]]
	    then
	        descriptions[$i]=`cat temp.txt | grep 'div id=\"detailDescription\"' | sed 's/^ *<div id="detailDescription">//g' | sed 's/<\/div>//g'`

	    else
	        descriptions[$i]=`cat temp.txt | awk '/class=\"job_description\"/,/<\/div>/' | grep \<p\> | sed 's/^[^<]*<p>//g' | sed 's/\<\/p\>[^a-z]*$//g'`
	    fi
	
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
    offset=$(( offset+50 ))
done
