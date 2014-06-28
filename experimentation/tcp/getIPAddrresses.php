<?php
$domain = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX);
$hostname=gethostname();
$ips=gethostbynamel($hostname);

echo "$domain $hostname\n";
print_r($ips);

?>