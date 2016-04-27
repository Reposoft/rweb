#!/bin/bash
docker-compose up -d
docker-compose exec svn repocreate test1 -o www-data
