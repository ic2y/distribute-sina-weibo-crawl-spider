#!/bin/bash

PROCESS=`ps -ef | grep main.py | grep -v grep | grep -v PPID | awk '{print $2}'`
for i in $PROCESS
do
  echo "Kill the  process [ $i ]"
  kill $i
done
cd ~/soft/weibo_distributed/

nohup python main.py &
