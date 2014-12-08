#! /bin/bash

source ../dbcredentials.sh

query=$1
zip=$2

https://jobs.github.com/positions?description=devops&location=
#Count how many results there are

results=`curl -L 'https://jobs.github.com/positions' | grep "Showing 1 -" | sed 's/^.*of \([0-9]\+\)<\/h2>/\1/' | sed 's/^.*of \([0-9]\+\)/\1/'`
#(pulls all jobs on the site; no parameters on this one)


echo "STARTING: $results RESULTS TO PARSE"
offset=0

page=0
while [[ $offset -lt $results ]]
do
    curl -G 'https://jobs.github.com/positions' -d page=$page > listings.txt
    #Strip out everything but the links to the job listings
    cat listings.txt | grep "<a href=\"/positions/" > listings2.txt
    mv listings2.txt listings.txt

    #strip out the leading spaces and stuff
    sed -i 's/^.*<h4>//' listings.txt


    #clean up the links, convert them to absolute URLs
    sed -i 's|/positions/|http://jobs.github.com/positions/|' listings.txt
    declare -a titles
    declare -a urls
    declare -a descriptions
    declare -a companies

#Collect the job links
    let i=0
    while read line
    do
        #grab the URL, strip out any parameters
    urls[$i]=`echo $line | sed 's/<a href="\([^\"]*\)">.*$/\1/g'`
    echo "URL IS |${urls[$i]}|"
    #grab the title
    titles[$i]=`echo $line | sed 's/<a [^>]*>\(.*\)<\/a><\/h4>$/\1/g'`
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

    	    companies[$i]=`cat temp.txt | grep "a href=\"/companies" | sed 's/[^"]*"\/companies\/\([^"]*\)".*$/\1/g'`

    	    #if the company isn't found with that regex
    	    if [[ -z ${companies[$i]} ]]; then
        	    echo "Company name not found; using alternate search."
        
        	    lookahead=1000
        	    while read line
        	    do
        	    if [[ $lookahead -eq 0 ]]; then
            	    companies[$i]="$line"
            	    echo "Company is now |${companies[$i]}|"
            	    lookahead=-1
        	    else
            	        lookahead=$(( lookahead-1 ))
        	    fi

        	    if [[ `echo $line | grep -c "<div class=\"module logo\">"` -gt 0 ]]
        	    then
            	    echo "Found the target div!"
           	    lookahead=3
        	    fi      
        	    done < "temp.txt"
    	    fi

    	    echo "${titles[$i]} is at ${companies[$i]}"

    	    #get description
    	    saving=0
    	    while read line
    	    do
		if [[ $saving -eq 1 ]]; then
	            if [[ `echo $line | grep -c "<!-- /.column.main -->"` -eq 0 ]]; then
	                echo $line >> "description.txt"
    	            else
	            saving=0
	            fi
		fi

		if [[ `echo $line | grep -c "div class=\"column main \""` -eq 1 ]]; then
	    	    saving=1
		fi
    	    done < "temp.txt"

    descriptions[$i]=`cat description.txt`
    rm description.txt
	
    #escape the mysql special characters (and HTML)
	urls[$i]=`echo ${urls[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\\;/g' | sed 's/>/\&gt\\;/g';`
	titles[$i]=`echo ${titles[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\\;/g' | sed 's/>/\&gt\\;/g';`
	descriptions[$i]=`echo ${descriptions[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\\;/g' | sed 's/>/\&gt\\;/g';`

	SQL=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
INSERT INTO listings (url, company, title, description) VALUES ('${urls[$i]}', '${companies[$i]}', '${titles[$i]}', '${descriptions[$i]}')
EOF`	
	fi
	i=$(( i+1 ))
    done
    page=$(( page+1 ))
    offset=$(( offset+50 ))
done

