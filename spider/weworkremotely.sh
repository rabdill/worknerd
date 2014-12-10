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
