#!/bin/bash

# config
PLUGINDIR=$(pwd)
MAINFILE="wp-mpdf.php"

# git config
GITPATH="$PLUGINDIR/" # this file should be in the base of your git repository

# Check that git working directory is clean
if [ -n "$(git status --porcelain)" ]; then
  echo "There are uncommitted git changes. Exiting...."
  exit 1
fi

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
PLUGINVERSION=$(grep "Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}' | tr -d '\r')
echo "$MAINFILE version: $PLUGINVERSION"
READMEVERSION=$(grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}' | tr -d '\r')
echo "readme.txt version: $READMEVERSION"
READMEMDVERSION=$(grep "^\*\*Stable tag:\*\*" $GITPATH/readme.md | awk -F' ' '{print $NF}' | tr -d '\r')
echo "readme.md version: $READMEMDVERSION"

if [ "$READMEVERSION" = "trunk" ]; then
  echo "Version in readme.txt & $MAINFILE don't match, but Stable tag is trunk. Let's proceed..."
elif [ "$PLUGINVERSION" != "$READMEVERSION" ]; then
  echo "Version in readme.txt & $MAINFILE don't match. Exiting...."
  exit 1
elif [ "$PLUGINVERSION" != "$READMEMDVERSION" ]; then
  echo "Version in readme.md & $MAINFILE don't match. Exiting...."
  exit 1
elif [ "$PLUGINVERSION" = "$READMEVERSION" ] && [ "$PLUGINVERSION" = "$READMEMDVERSION" ]; then
  echo "Versions match in readme.txt, readme.md and $MAINFILE. Let's proceed..."
fi

if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"; then
  echo "Version $PLUGINVERSION already exists as git tag. Exiting...."
  exit 1
else
  echo "Git version does not exist. Let's proceed..."
fi

# Create the actual tag
echo "Tagging new version in git"
git tag -a "$PLUGINVERSION" -m "Tagging version $PLUGINVERSION"

# Push changes
echo "Pushing git master to origin, with tags"
git push origin master --tags
