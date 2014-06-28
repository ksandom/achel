#!/usr/bin/env php
<?php
error_reporting(E_ALL);

/* From: http://php.net/manual/en/sockets.examples.php
 Modifications:
	Javier: Concurrent connections
	Tomasz: Non-blocking on disconnect
	Kevin: Added say
*/

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '127.0.0.1';
$port = 10001;

 if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
     echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
 }

 if (socket_bind($sock, $address, $port) === false) {
     echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
 }

 if (socket_listen($sock, 5) === false) {
     echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
 }

//clients array
$clients = array();

 do {
     $read = array();
     $read[] = $sock;
     
     $read = array_merge($read,$clients);
     
     // Set up a blocking call to socket_select
     $write = NULL;
     $except = NULL;
     if(socket_select($read,$write, $except, $tv_sec = 5) < 1)
     {
         //    SocketServer::debug("Problem blocking socket_select?");
         continue;
     }
     
     // Handle new Connections
     if (in_array($sock, $read)) {        
         
         if (($msgsock = socket_accept($sock)) === false) {
             echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
             break;
         }
         $clients[] = $msgsock;
         $key = array_keys($clients, $msgsock);
         /* Enviar instrucciones. */
         $msg = "\nWelcome to the PHP Test Server.\n" .
         "You are client: {$key[0]}\n" .
         "To quit, type 'quit'. 'say something' to say something to every node connected. To shut down the server type 'shutdown'.\n";
         socket_write($msgsock, $msg, strlen($msg));
         
     }
     
     // Handle Input
     foreach ($clients as $key => $client) { // for each client        
         if (in_array($client, $read)) {
             if (false === ($buf = socket_read($client, 2048, PHP_NORMAL_READ))) {
                 echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($client)) . "\n";
                 unset($clients[$key]);
                 socket_close($client);
                 break;
             }             if (!$buf = trim($buf)) {
                 continue;
             }
             if ($buf == 'quit') {
                 unset($clients[$key]);
                 socket_close($client);
                 break;
             }
             if ($buf == 'shutdown') {
                 socket_close($client);
                 break 2;
             }
             
             $threeLeterWord=substr($buf, 0, 3);
             if ($threeLeterWord=='say')
             {
		foreach ($clients as $subkey => $client)
		{
			$talkback="Client {$subkey}: '$buf'.\n";
			socket_write($client, $talkback, strlen($talkback));
		}
             }
             $talkback = "Cliente {$key}: Usted dijo '$buf'.\n";
             socket_write($client, $talkback, strlen($talkback));
             echo "$buf\n";
         }
         
     }
     echo "### loop\n";
 } while (true);

socket_close($sock);
?>