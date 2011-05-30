

if [ ! -e jetty ]
then

# could not find the jsp jars in jetty7
#wget "http://www.eclipse.org/downloads/download.php?file=/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&url=http://ftp.ing.umu.se/mirror/eclipse/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&mirror_id=494"
#tar xvzf jetty-distribution-7.0.0.v20091005.tar.gz
#mv jetty-distribution-7.0.0.v20091005 jetty
JETTY_VERSION=6.1.23
wget http://dist.codehaus.org/jetty/jetty-$JETTY_VERSION/jetty-$JETTY_VERSION.zip
unzip jetty-$JETTY_VERSION.zip
mv jetty-$JETTY_VERSION jetty
rm jetty-*

fi

if [ ! -e jetty/webapps/solr.war ]
then

# install solr
SOLR_VERSION=1.4.1
rm apache-solr-*
wget http://apache.dataphone.se/lucene/solr/$SOLR_VERSION/apache-solr-$SOLR_VERSION.tgz
tar xvzf apache-solr-$SOLR_VERSION.tgz
rm jetty/webapps/cometd.war	# takes some time to extract
mv apache-solr-$SOLR_VERSION/dist/apache-solr-$SOLR_VERSION.war jetty/webapps/solr.war
rm -Rf apache-solr-*

fi

pushd jetty

#jetty7
#java -jar start.jar OPTIONS=default,jsp -Djetty.port=8080 -Dsolr.solr.home=/Volumes/Encrypted/workspace/cms/solr/home/

#SOLRHOME="$HOME/workspace/cms/solr/home/"
SOLRHOME="$(pwd)/../../../../../../cms/solr/home/"	# any workspace
#java -Djetty.port=8080 -Dsolr.solr.home=$HOME/workspace/cms/solr/home/ -jar
java -Djetty.port=8080 -Dsolr.solr.home=$(pwd)/../../../../../../cms/solr/home/ -jar start.jar etc/jetty.xml etc/jetty-ajp.xml

popd

