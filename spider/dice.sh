#! /bin/bash

source ../dbcredentials.sh

query=$1
zip=$2

#Count how many results there are
results=`curl -G -v http://www.dice.com/job/results/${zip} -d caller=basic -d q=${query} -d x=all -d p=z -d n=50 | grep "Search results" | sed 's/^.*of \([0-9]\+\)<\/h2>/\1/'`

echo "STARTING: $results RESULTS TO PARSE"
offset=0

while [[ $offset -lt $results ]]
do
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
	#read the job listing into a file
	curl -L ${urls[$i]} > temp.txt

	#scrape out the salary section
	salaries[$i]=`cat temp.txt | grep baseSalary | sed 's/ *<span property="baseSalary" content="\([^"]*\)"><\/span>/\1/g'`

	#figure out which format is on the page, scrape it
	if [[ `cat temp.txt | grep -c 'div id=\"detailDescription\"'` -gt 0 ]]
	then
	    descriptions[$i]=`cat temp.txt | grep 'div id=\"detailDescription\"' | sed 's/^ *<div id="detailDescription">//g' | sed 's/<\/div>//g'`
	    companies[$i]=`cat temp.txt | grep \<h2\>for | sed 's/.*>for \(.*\) in .*$/\1/g'`

	else
	    descriptions[$i]=`cat temp.txt | awk '/class=\"job_description\"/,/<\/div>/' | grep \<p\> | sed 's/^[^<]*<p>//g' | sed 's/\<\/p\>[^a-z]*$//g'`
	    companies[$i]=`cat testing.txt | grep 'a href="/jobsearch/company' | sed 's/\s*<dd><.*>\(.*\)<\/a><\/dd>/\1/g'`
	    echo ${urls[$i]} >> to-research.txt
	fi

    #escape the mysql special characters
	urls[$i]=`echo ${urls[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g"`
	titles[$i]=`echo ${titles[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g"`
	salaries[$i]=`echo ${salaries[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g"`
	descriptions[$i]=`echo ${descriptions[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g"`

	SQL=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
INSERT INTO listings (url, company, title, salary, description) VALUES ('${urls[$i]}', '${companies[$i]}', '${titles[$i]}', '${salaries[$i]}', '${descriptions[$i]}')
EOF`	

	i=$(( i+1 ))
    done
    offset=$(( offset+50 ))
done
