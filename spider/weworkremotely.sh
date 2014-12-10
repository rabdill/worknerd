#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

query=$1
zip=$2
for page_num in $(seq 2 6 7)
do
	curl "https://weworkremotely.com/categories/${page_num}/jobs" > listings.txt
	echo "https://weworkremotely.com/categories/${page_num}/jobs"
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
		    titles[$i]=`cat temp.txt | grep \<h1\> | sed 's/^[^>]*\?>\(.*\?\)<\/h1>.*$/\1/g'`
		    
		    #scrape company
		    companies[$i]=`cat temp.txt | grep "<span class=\"company\">" | sed 's/^[^>]*\?>\(.*\?\)<\/span>.*$/\1/g'`

		    #scrape description
		    found=0
		    while read line
		    do
		    	if [[ $found == 1 ]]; then
		    		descriptions[$i]=$line
		    		echo "description is |${descriptions[$i]}|"
		    		break
		    	else
			    	if [[ `echo $line | grep -c "<div class=\"listing-container\">"` -gt 0 ]]
			    	then
		    	    	echo "Found the target div!"
		   	    	    found=1
			    	fi
			    fi      
		    done < "temp.txt"

		    

			#escape the mysql special characters (and HTML)
			urls[$i]=`echo ${urls[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
			companies[$i]=`echo ${companies[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
			titles[$i]=`echo ${titles[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
			descriptions[$i]=`echo ${descriptions[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g";`


			SQL=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
			INSERT INTO unprocessed (url, company, title, description) VALUES ('${urls[$i]}', '${companies[$i]}', '${titles[$i]}', '${descriptions[$i]}')
EOF`
		fi	
		i=$(( i+1 ))
	done
done