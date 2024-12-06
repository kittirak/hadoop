#!/bin/bash


if [ $# -eq 0 ] then
	echo "Usage: $0 [user file] [--gid= (optional)]"
fi

whil getopts ":

for i in `cat user.list`
do
  FIRST=`echo $i | cut -d, -f1`
  LAST=`echo $i | cut -d, -f2`
  USER=$FIRST${LAST:0:1}
  USERLOWER=${USER,,}
  # echo "$USERLOWER $FIRST $LAST"
  # User Lastname for password
  echo ${LAST,,} | ipa user-add $USERLOWER --first=$FIRST --last=$LAST --gidnumber=1932000016 --shell=/bin/bash --password

done
