

if [ ! -e jetty ]
then

# could not find the jsp jars in jetty7
#wget "http://www.eclipse.org/downloads/download.php?file=/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&url=http://ftp.ing.umu.se/mirror/eclipse/jetty/7.0.0.v20091005/dist/jetty-distribution-7.0.0.v20091005.tar.gz&mirror_id=494"
#tar xvzf jetty-distribution-7.0.0.v20091005.tar.gz
#mv jetty-distribution-7.0.0.v20091005 jetty

wget http://dist.codehaus.org/jetty/jetty-6.1.21/jetty-6.1.21.zip
unzip jetty-6.1.21.zip
mv jetty-6.1.21 jetty
# until there is a 1.4 distribution war
cp ../../../../../solr/dist/apache-solr-2009-10-15_08-05-43.war jetty/webapps/solr.war

rm jetty-*

fi

cd jetty

#jetty7
#java -jar start.jar OPTIONS=default,jsp -Djetty.port=8080 -Dsolr.solr.home=/Volumes/Encrypted/workspace/cms/solr/home/

java -Djetty.port=8080 -Dsolr.solr.home=/Volumes/Encrypted/workspace/cms/solr/home/ -jar start.jar
