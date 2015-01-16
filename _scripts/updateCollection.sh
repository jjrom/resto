#!/bin/bash
#
# RESTo
# 
#  RESTo - REstful Semantic search Tool for geOspatial 
# 
#  Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
# 
#  jerome[dot]gasperi[at]gmail[dot]com
#  
#  
#  This software is governed by the CeCILL-B license under French law and
#  abiding by the rules of distribution of free software.  You can  use,
#  modify and/ or redistribute the software under the terms of the CeCILL-B
#  license as circulated by CEA, CNRS and INRIA at the following URL
#  "http://www.cecill.info".
# 
#  As a counterpart to the access to the source code and  rights to copy,
#  modify and redistribute granted by the license, users are provided only
#  with a limited warranty  and the software's author,  the holder of the
#  economic rights,  and the successive licensors  have only  limited
#  liability.
# 
#  In this respect, the user's attention is drawn to the risks associated
#  with loading,  using,  modifying and/or developing or reproducing the
#  software by the user in light of its specific status of free software,
#  that may mean  that it is complicated to manipulate,  and  that  also
#  therefore means  that it is reserved for developers  and  experienced
#  professionals having in-depth computer knowledge. Users are therefore
#  encouraged to load and test the software's suitability as regards their
#  requirements in conditions enabling the security of their systems and/or
#  data to be ensured and,  more generally, to use and operate it in the
#  same conditions as regards security.
# 
#  The fact that you are presently reading this means that you have had
#  knowledge of the CeCILL-B license and that you accept its terms.
#  
HTTPS=0
HOST=localhost
TARGET=resto2
usage="## Update a collection\n\n  Usage $0 -c <Collection name> -f <Collection description file (i.e. Text file with data=xxx where xxx is the encodeURIComponent of the JSON collection description)>  -u <username:password> [-s (use https if set) -H server (default localhost) -p resto path (default resto)]\n"
while getopts "c:sf:up::hH:" options; do
    case $options in
        c ) COLLECTION=`echo $OPTARG`;;
        H ) HOST=`echo $OPTARG`;;
        p ) TARGET=`echo $OPTARG`;;
        u ) AUTH=`echo $OPTARG`;;
        f ) JSON=`echo $OPTARG`;;
        s ) HTTPS=1;;
        h ) echo -e $usage;;
        \? ) echo -e $usage
            exit 1;;
        * ) echo -e $usage
            exit 1;;
    esac
done
if [ "$JSON" = "" ]
then
    echo -e $usage
    exit 1
fi
if [ "$COLLECTION" = "" ]
then
    echo -e $usage
    exit 1
fi

if [ "$HTTPS" = "1" ]
then
    curl -k -X PUT -d @$JSON https://$AUTH@$HOST/$TARGET/collections/$COLLECTION
else
    curl -X PUT -d @$JSON http://$AUTH@$HOST/$TARGET/collections/$COLLECTION
fi
echo ""
