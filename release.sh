#!/bin/bash
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/GaryJones/wordpress-plugin-git-flow-svn-deploy
# The difference is that this script lives in the plugin's git repo, auto sets parameters and checks for a readme.md in addition to readme.txt

# config
PLUGINDIR=`pwd`
MAINFILE="wp-mpdf.php"

SVNURL="https://plugins.svn.wordpress.org/wp-mpdf/"
SVNUSER="fkrauthan"
SVNPATH="$PLUGINDIR/svn"


# git config
GITPATH="$PLUGINDIR/" # this file should be in the base of your git repository

# Let's begin...
echo "............................................."
echo
echo "Preparing to deploy wp-mpdf WordPress plugin"
echo
echo "............................................."
echo

# Check that git working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    echo "There are uncommitted git changes. Exiting...."
	exit 1;
fi

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
PLUGINVERSION=`grep "Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}' | tr -d '\r'`
echo "$MAINFILE version: $PLUGINVERSION"
READMEVERSION=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}' | tr -d '\r'`
echo "readme.txt version: $READMEVERSION"
READMEMDVERSION=`grep "^**Stable tag:**" $GITPATH/readme.md | awk -F' ' '{print $NF}' | tr -d '\r'`
echo "readme.md version: $READMEMDVERSION"

if [ "$READMEVERSION" = "trunk" ]; then
	echo "Version in readme.txt & $MAINFILE don't match, but Stable tag is trunk. Let's proceed..."
elif [ "$PLUGINVERSION" != "$READMEVERSION" ]; then
	echo "Version in readme.txt & $MAINFILE don't match. Exiting...."
	exit 1;
elif [ "$PLUGINVERSION" != "$READMEMDVERSION" ]; then
	echo "Version in readme.md & $MAINFILE don't match. Exiting...."
	exit 1;
elif [ "$PLUGINVERSION" = "$READMEVERSION" ] && [ "$PLUGINVERSION" = "$READMEMDVERSION" ]; then
	echo "Versions match in readme.txt, readme.md and $MAINFILE. Let's proceed..."
fi

if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
	then
		echo "Version $PLUGINVERSION already exists as git tag. Exiting....";
		exit 1;
	else
		echo "Git version does not exist. Let's proceed..."
fi


# Tag new version
echo "Tagging new version in git"
git tag -a "$PLUGINVERSION" -m "Tagging version $PLUGINVERSION"


# Push changes back up to master
#echo "Pushing git master to origin, with tags"
#git push origin master
#git push origin master --tags


# Checkout svn repo
echo
echo "Creating local copy of SVN repo trunk ..."
svn checkout $SVNURL $SVNPATH --depth immediates
svn update --quiet $SVNPATH/trunk --set-depth infinity

echo "Exporting the HEAD of master from git to the trunk of SVN"
rm -Rf $SVNPATH/trunk/
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Ignoring GitHub specific files"
svn propset svn:ignore "readme.md
Thumbs.db
.github/*
.git
.gitattributes
release.sh
.gitignore" "$SVNPATH/trunk/"

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Delete all files that should not now be added.
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | sed 's/! *//' | xargs -I% svn del --force %
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | sed 's/\? *//' | xargs svn add
svn commit --username=$SVNUSER -m "Preparing for $PLUGINVERSION release"


echo "Creating new SVN tag and committing it"
cd $SVNPATH
svn update --quiet $SVNPATH/tags/$PLUGINVERSION
svn copy --quiet trunk/ tags/$PLUGINVERSION/

# Commit new tag
cd $SVNPATH/tags/$PLUGINVERSION
svn commit --username=$SVNUSER -m "Tagging version $PLUGINVERSION"

echo "Removing temporary directory $SVNPATH"
cd $SVNPATH
cd ..
rm -fr $SVNPATH/

echo "*** FIN ***"
