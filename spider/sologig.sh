#! /bin/bash

source /var/www/html/worknerd/dbcredentials.sh

query=$1
max=$2
zip=$3


curl -GL http://www.sologig.com/jobs/ -d Keywords=${query} -d Radius=30 -d PageNumber=1 -d OrderBy=Date > listings.txt
#Strip out everything but the links to the job listings
cat listings.txt | grep "\" class=\"result-left\"" | sed 's/^.*href="\(.*\)" .*$/\1/' > listings2.txt
mv listings2.txt listings.txt

#clean up the links, convert them to absolute URLs
sed -i 's|/jobs/|http://www.sologig.com/jobs/|' listings.txt
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
            urls[$i]=`echo $line`
            i=$(( i+1 ))
done < listings.txt

#Hit up the job pages
total=${#urls[*]}

if [[ $total -gt $max ]]; then
    echo "WARN: Potential results exceeds max crawl number. Reducing target down to ${max}."
    total=$max
fi


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

        #grab the title
            titles[$i]=`cat temp.txt | grep "<title>" | sed 's/\s*<\/*title>//g' | sed 's/\(.*\) in US-.\+$/\1/'`
            echo "TITLE IS |${titles[$i]}|";

        #get company
        lookahead=100000
        while read line
        do
            if [[ $lookahead -eq 0 ]]; then
                companies[$i]="$line"
                echo "Company is now |${companies[$i]}|"
                break
            else
                lookahead=$(( lookahead-1 ))
            fi

            if [[ `echo $line | grep -c "<div class=\"header-byline\">"` -gt 0 || `echo $line | grep -c "zhdr1\""` -gt 0 ]]
            then
                echo "Found the target div!"
                lookahead=1
            else
                if [[ `echo $line | grep -c "Click Here to Navigate To the Company Web Site"` -gt 0 ]]; then
                    companies[$i]=`echo $line | sed 's/^.*>\(.*\)<\/a>.*$/\1/'`
                    break
                fi
            fi      
        done < "temp.txt"
        
        #get description
        saving=0
        while read line
        do
            if [[ $saving -eq 1 ]]; then
                    if [[ `echo $line | grep -c "<div id"` -eq 0 && `echo $line | grep -c "<div class=\"h2\">Company Overview"` -eq 0 && `echo $line | grep -c "<table width"` -eq 0 ]]; then
                            echo $line >> "description.txt"
                            #echo "Just wrote |$line| to file:"
                        else
                            saving=0
                            #echo "Not interesting: |$line|"
                    fi
            fi
            if [[ `echo $line | grep -c "<div id=\"description-container-"` -eq 1 || `echo $line | grep -c "jobs-detail-job"` -eq 1 || `echo $line | grep -c "<p class=\"h.*2*\">Job Description"` -eq 1 ]]; then
                    saving=1
                    echo "TIME TO GO!"
            fi
        done < "temp.txt"
        descriptions[$i]=`cat description.txt`
        rm description.txt


    
#escape the mysql special characters (and HTML)
    urls[$i]=`echo ${urls[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
    titles[$i]=`echo ${titles[$i]} | sed 's/[)(%"\\]/\\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`
    salaries[$i]=`echo ${salaries[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt;/g' | sed 's/>/\&gt\;/g';`
    descriptions[$i]=`echo ${descriptions[$i]} | sed 's/[)(%"\\]/\\&/g' | sed "s/[']/\\\&/g" | sed 's/</\&lt\;/g' | sed 's/>/\&gt\;/g';`

    SQL=`mysql -s -r -N -h $dbendpoint -D results -u $dbuser -p$dbpassword <<EOF
INSERT INTO unprocessed (url, company, title, description) VALUES ('${urls[$i]}', '${companies[$i]}', '${titles[$i]}', '${descriptions[$i]}')
EOF`
    fi	
    i=$(( i+1 ))
done
    #offset=$(( offset+50 ))
#done
