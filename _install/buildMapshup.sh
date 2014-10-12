#!/bin/bash
#
# Dedicated build of mapshup for the RESTo framework (http://github.com/jjrom/resto)
#
# Web client build script
#
# Author : Jerome.Gasperi@gmail.com
# Date   : 2014.02.08
# Version: 1.0
#

# Set default values - can be superseeded by command line
SRC=/tmp/mapshup-src
BUILDDIR=/tmp/mapshup-build
RESTO_HOME=../
COMPILE=NO
CLONE=NO

usage="## mapshup client build script for RESTo\n\n  Usage $0 [-a]\n\n  -a : performs steps 1. mapshup git clone, 2. mapshup compile and 3. install mapshup\n  (By default, only installation step is performed)\n"
while getopts "ah" options; do
    case $options in
        a ) CLONE=YES
            COMPILE=YES;;
        h ) echo -e $usage;;
        \? ) echo -e $usage
            exit 1;;
        * ) echo -e $usage
            exit 1;;
    esac
done

# git clone
if [ "$CLONE" = "YES" ]
then
    echo -e " -> Clone mapshup git repository to $SRC directory"   
    git clone https://github.com/jjrom/mapshup.git $SRC
fi

if [ "$COMPILE" = "YES" ]
then
    echo -e " -> Compile mapshup to $BUILDDIR directory"
    /bin/rm -Rf $BUILDDIR
    $SRC/utils/packer/pack.sh $SRC $BUILDDIR default 0 mapshup/buildfile.txt 0
fi

if [ ! -d $BUILDDIR ]
then
    echo "$BUILDDIR directory does not exist. Launch $0 -a"
    exit 1
fi

echo -e " -> Copy mapshup javascript to $RESTO_HOME/_install/js directory"
cp -Rf $BUILDDIR/js/mapshup $RESTO_HOME/_install/js/
rm -Rf $RESTO_HOME/_install/js/mapshup/theme/blacker
echo -e " -> Copy jquery javascript to $RESTO_HOME/_install/js directory"
cp -Rf $BUILDDIR/js/mjquery $RESTO_HOME/_install/js
echo -e " -> Copy OpenLayers javascript to $RESTO_HOME/_install/js directory"
cp -Rf $BUILDDIR/js/mol $RESTO_HOME/_install/js
echo -e " -> Copy mapshup javascript configuration file to $RESTO_HOME/themes/default directory"
cp -f mapshup/config.js $RESTO_HOME/themes/default/config.js
echo -e " -> done!\n"
