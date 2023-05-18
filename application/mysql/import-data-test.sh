#!/bin/bash


docker exec -it userapi-mysql-1  sh -c 'mysql -uroot -proot -e "SET global wait_timeout = 31536000; SET global max_allowed_packet=1073741824;"'
docker exec -it userapi-mysql-1  sh -c 'mysql -uroot -proot <  ./application/mysql/interview_data.sql'