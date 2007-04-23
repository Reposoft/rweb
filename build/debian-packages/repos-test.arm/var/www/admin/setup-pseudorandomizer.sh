#!/bin/sh

# The server may run out of entropy for the randomizer, 
# which makes subversion commits terribly slow.
# Run this script after every boot to setup pseudorandom instead
# For more info see http://www.random.org/randomness/
mv /dev/random /dev/random_orig
mknod /dev/random c 1 9
chmod ug+w /dev/random
