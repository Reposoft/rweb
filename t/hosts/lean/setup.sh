#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
pushd "$DIR"

#svn co http://trac-hacks.org/svn/xmlrpcplugin/trunk xmlrpcplugin
#sudo easy_install -Z -U xmlrpcplugin/

trac-admin trac/sample1 initenv "Sample1 backend" sqlite:db/trac.db

trac-admin trac/sample1/ permission add authenticated XML_RPC
trac-admin trac/sample1/ permission add anonymous XML_RPC


popd

