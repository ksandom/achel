#!/bin/bash
# Gives the average CPU *load* and memory usage.
# Note that these metrics are purely for getting some interesting data. If you use them for other purposes they are likely to be not entirely correct depending on your needs. Eg cache and buffers are not taken into account for memory, and messing around with the load average in this way is a bit miss-leading.


cores=`grep 'vendor_id' /proc/cpuinfo | wc -l`

function getCPULoad
{
	totalLoad=`uptime | sed 's/^.*load average: //g;s/, .*$//g;s/\.//g;s/^0*//g'`
	let averageLoad=$totalLoad/$cores
	echo $averageLoad
}

function getMemory
{
	read label total used free shared buffers cached < <(free | head -n 2 | tail -n 1)
	let percentUsed=$used*100
	let percentUsed=$percentUsed/$total
	echo $percentUsed
}


while true;do
	cpu=`getCPULoad`
	mem=`getMemory`
	echo "{\"cpu\":\"$cpu\", \"cores\":\"$cores\", \"memory\":\"$mem\"}"
	sleep 1
done
