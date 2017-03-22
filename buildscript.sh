#!/bin/bash

# START FUNCTION DEFINITIONS #

function afterBuild()
{

  local dir=$(pwd)

  if [ -d "$dir/build/bundled" ]; then

    # Remove unbundled directory because it will never be used :)
    if [ -d "$dir/build/unbundled" ]; then
      # Exists
      rm "$dir/build/unbundled" -r
      echo build/unbundled: directory removed
    fi

    dir="$dir/build/bundled"
    cd ${dir}

    # Absolute paths, if it does not work, you are not working with the resources
    # for which this script is made for :)

    # Rename dbconf file and delete the one with the personal data
    if [ -f "$dir/backend/db/conf/dbconf_template.php" ]; then
      # template exists => replace original with template
      cd backend/db/conf
      rm dbconf.php
      echo dbconf.php deleted
      mv dbconf_template.php dbconf.php
      echo renamed dbconf_template.php to dbconf.php
    fi

    cd ${dir}
    cd backend/accountsystem

    # Replace local directory paths with production-ready paths
    sed -i 's/$HTTPS_ONLY = false;/$HTTPS_ONLY = true;/' sessioncontroller.class.php
    echo set HTTPS_ONLY to true
    sed -i 's/localhost//' sessioncontroller.class.php
    echo removed localhost root as directory limit for PHP sessions

    echo
    echo Build finished without errors!
    echo
    echo Last thing to do: Replace \"var PrecacheConfig\" in line 34 of the \"build/bundled/service-worker.js\" - File :\)

  else
    echo Something went wrong whilst building...
    echo Quitting build routine...
    exit 0
  fi
}

# END FUNCTION DEFINITIONS #

echo Running build...

# Declare current directory
dir=$(pwd)

if [ ! -d "$dir/bower_components" ] && [ ! -f "$dir/polymer.json" ]; then
  echo This directory does not seem to be initialized yet. Run polymer init to start a new Polymer project!
  exit 0
else
  if [ ! -d "$dir/build" ]; then
    echo Polymer seems to be initialized, continue...
    echo

    echo "Please enter the file name of the sw-precache-config if existant: "
    echo "(leave empty if you do not have an extra precache config file)"
    printf "> "
    read preCache

    if [ ! -z "$preCache" ]; then
      if [ ! -f "$dir/$preCache" ]; then
        echo The file you entered as sw-precache-config could not be found. Please recheck your spelling or create it.
        exit 0
      else
        echo Building with precache config \'${preCache}\'...
        polymer build --sw-precache-config ${preCache}
        # Call function afterBuild
        afterBuild
        exit 0
      fi
    else
      echo Continuing without custom sw-precache-config...
      polymer build
      # Call Function afterBuild
      afterBuild
      exit 0
    fi
  else
    echo There is already a build existant...
    echo Continuing with the afterBuild routine...
    afterBuild
    exit 0
  fi
fi