

if [ ! -e jetty ]
then

# could not find the jsp jars in jetty7
#wget "http://www.eclipse.org/downloads/download.php?file=/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&url=http://ftp.ing.umu.se/mirror/eclipse/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&mirror_id=494"
#tar xvzf jetty-distribution-7.0.0.v20091005.tar.gz
#mv jetty-distribution-7.0.0.v20091005 jetty

wget http://dist.codehaus.org/jetty/jetty-6.1.23/jetty-6.1.23.zip
unzip jetty-6.1.23.zip
mv jetty-6.1.23 jetty
rm jetty-*

fi

if [ ! -e jetty/webapps/solr.war ]
then

# install solr
wget http://apache.dataphone.se/lucene/solr/1.4.0/apache-solr-1.4.0.tgz
tar xvzf apache-solr-1.4.0.tgz
mv apache-solr-1.4.0/dist/apache-solr-1.4.0.war jetty/webapps/solr.war
rm -Rf apache-solr-*

fi

cd jetty

#jetty7
#java -jar start.jar OPTIONS=default,jsp -Djetty.port=8080 -Dsolr.solr.home=/Volumes/Encrypted/workspace/cms/solr/home/

java -Djetty.port=8080 -Dsolr.solr.home=/Volumes/Encrypted/workspace/cms/solr/home/ -jar start.jar etc/jetty.xml etc/jetty-ajp.xml

