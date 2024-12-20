<?php
# Copyright (c) 2014-2018, Kevin Sandom under the GPL License. See LICENSE for full details.
# Provides Network Faucets.

# TODO s
/*
From http://us2.php.net/function.fsockopen - Note: If you need to set a timeout for reading/writing data over the socket, use stream_set_timeout(), as the timeout parameter to fsockopen() only applies while connecting the socket.


*/



class NetworkFaucets extends Faucets
{
	function __construct()
	{
		parent::__construct(__CLASS__);
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('createSocketServerFaucet'), 'createSocketServerFaucet', "Creates a listening TCP socket. --createSocketServerFaucet=faucetName,connectEventName,disconnectEventName,[closeEventName],port[,bindAddress] . bindAddress defaults to all interfaces. The closeEventName defaults to the disconnectEventName", array('network', 'faucet'));
				$this->core->registerFeature($this, array('createSocketClientFaucet'), 'createSocketClientFaucet', "Creates a TCP socket connecting to a specific address. --createSocketClientFaucet=faucetName,connectEventName,disconnectEventName,[closeEventName],port,address . bindAddress defaults to all interfaces. The closeEventName defaults to the disconnectEventName", array('network', 'faucet'));

				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'createSocketServerFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 6, 4);
				$faucet=new SocketServerFaucet($parms[1], $parms[2], $parms[3]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'SocketServer', $faucet);
				$faucet->listen($parms[5], $parms[4]);
				break;
			case 'createSocketClientFaucet':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 6, 4);
				$faucet=new SocketServerFaucet($parms[1], $parms[2], $parms[3]);
				$this->environment->currentFaucet->createFaucet($parms[0], 'SocketServer', $faucet);
				$faucet->connect($parms[5], $parms[4]);
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}
}

class SocketServerFaucet extends ThroughBasedFaucet
{
	private $socket=false;
	private $clients=null;
	private $clientTracker=null;
	protected $inputBuffer=null;
	private $connectEvent='';
	private $disconnectEvent='';
	private $closeEvent='';
	private $establishTimeout=5; // Timeout for establishing a connection.
	private $isListener=false;
	private $inEOL="\r";
	private $outEOL="\n";
	private $expectEOL=false;

	function __construct($connectEvent, $disconnectEvent, $closeEvent)
	{
		parent::__construct(__CLASS__);

		$this->clients=array();
		$this->clientTracker=array();
		$this->inputBuffer=array();

		$this->connectEvent=$connectEvent;
		$this->disconnectEvent=$disconnectEvent;
		$this->closeEvent=($closeEvent)?$closeEvent:$disconnectEvent;
		$this->debug($this->l2, "connect=$connectEvent disconnect=$disconnectEvent close=$closeEvent");
	}

	public function listen($address, $port)
	{
		$this->isListener=true;
		$this->debug($this->l2,"listen: address=$address port=$port");
		if ($address) return $this->listenOnSpecificAddress($address, $port);
		else return $this->listenOnAllAddresses($port);
	}

	public function connect($address, $port)
	{
		$this->debug($this->l3, "SocketServerFaucet->connect($address, $port)");
		$this->socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 1000));

		if (socket_connect($this->socket, $address, $port)===false)
		{
			$this->debug($this->l2, "SocketServerFaucet->connect($address, $port): ".socket_strerror(socket_last_error($this->socket)));
			return false;
		}
		else
		{
			$this->debug($this->l2, "SocketServerFaucet->connect($address, $port): Success.");
			$this->clients['default']=&$this->socket;
			socket_set_nonblock($this->socket);
			return true;
		}
	}


	private function detectSocketDetails()
	{
		socket_getsockname($this->socket, $address, $port);
		$listenerDetails=array(
			'address'=>$address,
			'port'=>$port
			);

		$instanceName=$this->getInstanceName();
		$this->core->set($instanceName, 'listener', $listenerDetails);

		$this->debug($this->l2, "detectSocketDetails: Detected details ($address:$port) saved to $instanceName,listener");
	}

	private function listenOnAllAddresses($port)
	{
		/*
		There seems to be a bug somewhere surrounding socket_create_listen, which means we can not gracefully resume listening if PHP was restarted while there was an active connection. To work around this, you can either specify a listen address (eg localhost) or specify a port of 0 and then lookup the derived port in ~!faucetName,listener,port!~.
		*/

		$this->debug($this->l2, "SocketServerFaucet->listenOnAllAddresses($port)");
		$this->socket=socket_create_listen($port);
		if ($this->socket===false)
		{
			$this->debug($this->l1, "SocketServerFaucet->listenOnAllAddresses($port): Could not listen on port $port");
			$this->deconstruct();
			return false;
		}
		else
		{
			$this->detectSocketDetails();
			socket_set_nonblock($this->socket);
			socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
			return true;
		}
	}

	private function listenOnSpecificAddress($bindAddress, $port)
	{
		$this->socket=socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket===false)
		{
			$this->debug($this->l1, "SocketServerFaucet->listenOnSpecificAddress($bindAddress, $port): Could not create socket. \"".socket_strerror(socket_last_error())."\"");
		}
		else
		{
			socket_set_nonblock($this->socket);

			if (socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1))
			{
				if (socket_bind($this->socket, $bindAddress, $port)===false)
				{
					$this->debug($this->l1, "SocketServerFaucet->listenOnSpecificAddress($bindAddress, $port): Could not bind to address. \"".socket_strerror(socket_last_error($this->socket))."\"");
					$this->deconstruct();
				}
				else
				{
					if (socket_listen($this->socket)===false)
					{
						$this->debug($this->l1, "SocketServerFaucet->listenOnSpecificAddress($bindAddress, $port): Could not listen on address:$port. \"".socket_strerror(socket_last_error($this->socket))."\"");
						$this->deconstruct();
					}
					else
					{
						$this->detectSocketDetails();
						$this->debug($this->l2, "SocketServerFaucet->listenOnSpecificAddress($bindAddress, $port): Fully listening on $bindAddress:$port");
						return true;
					}
				}
			}
			else
			{
				$this->debug($this->l1, "SocketServerFaucet->listenOnSpecificAddress($bindAddress, $port): ".socket_strerror(socket_last_error($this->socket)));
				$this->deconstruct();
			}
		}
	}

	private function bindOnPort($address, $port, $limit=1000)
	{
		$categoryName=$this->getInstanceName();


	}

	function deconstruct()
	{
		# TODO make deconstruct be called on exit.

		foreach ($this->clients as $key=>$client)
		{
			$this->debug($this->l2, "SocketServerFaucet->deconstruct: Disconnecting from $key");
			socket_shutdown($client);
			socket_close($client);
			$this->triggerNetworkEvent($this->disconnectEvent, $key, 'disconnect');
		}

		$this->debug($this->l2, "SocketServerFaucet->deconstruct: Closing socket.");
		if (!is_null($this->socket) and !is_bool($this->socket))
		{
			// TODO This doesn't work in php8, but maybe it's still needed in some circumstances. I'm going to have to re-visit this before using it anyway, so will deal with it then.
			if (!is_numeric($this->socket))
			{
				// @socket_close($this->socket);
			}
		}
		$this->socket=null;

		parent::deconstruct();
	}

	function clientHasClosed($clientID)
	{
		$this->debug($this->l2, "SocketServerFaucet->clientHasClosed($clientID): Client $clientID has closed.");
		socket_close($this->clients[$clientID]);
		unset($this->clients[$clientID]);

		$this->triggerNetworkEvent($this->closeEvent, $clientID, 'close');
	}

	function checkForConnections()
	{
		if ($this->socket===null)
		{
			$this->debug($this->l1, "SocketServerFaucet->checkForConnections: Not connected.");
			return false;
		}

		if ($client=@socket_accept($this->socket))
		{
			socket_set_nonblock($client);
			$address='';
			$port='';

			# TODO The client IDs and channel IDs are the same thing. They should both have the same name variable.
			if (false) #(socket_getpeername ($this->socket, $address, $port))
			{
				$key="$address:$port";
				if (isset($this->clientTracker[$key])) $this->clientTracker[$key]++;
				else $this->clientTracker[$key]=0;

				$key="$key.{$this->clientTracker[$key]}";
				$this->clients[$key]=&$client;
			}
			else
			{
				$this->clients[]=&$client;

				# Derive what the last added key is
				$keys=array_keys($this->clients);
				$key=$keys[count($keys)-1];
			}

			$this->debug($this->l1, "SocketServerFaucet->checkForConnections: A client connected using key $key");
			# TODO The problem is that the key is not being passed. Instead the first parameter is being taken instead. See createSimpleNetworkServerFaucet.achel:15

			$this->triggerNetworkEvent($this->connectEvent, $key, 'connect');
		}
	}

	function triggerNetworkEvent($eventName, $key, $context)
	{
		$this->beginScopedEvent($this);

		if ($this->core->featureExists($eventName))
		{
			$this->debug($this->l3,"$context event key via feature: SocketServerFaucet,{$eventName},$key");
			$this->core->callFeature($eventName, $key);
		}
		else
		{
			$this->debug($this->l3,"$context event key via event: SocketServerFaucet,{$eventName},$key");
			$this->core->callFeature('triggerEvent', "SocketServerFaucet,{$eventName},$key");
		}

		$this->endScopedEvent();
	}

	// function callInFaucet($command, $parameters)
	// {
	// 	# Get the current path.
	// 	#$origin=$this->core->callFeature("pwd", '');
	// 	$env=&FaucetEnvironment::assert();
	// 	//$origin=$env->currentFaucet->getFullPath();
	// 	$origin=&$env->currentFaucet;
 //
	// 	# get the parent path.
	// 	$parentPath=$this->parent->getFullPath();
 //
	// 	if ($origin!=$parentPath)
	// 	{
	// 		$this->debug($this->l1,"Changing to $parentPath to run $command $parameters.");
	// 		# set the current path to the parent path.
	// 		$this->core->callFeature("changeFaucet", $parentPath);
 //
	// 		$this->core->callFeature($command, $parameters);
 //
	// 		$this->debug($this->l1,"callInFaucet: Changing back to the origin.");
	// 		$env->currentFaucet=&$origin;
	// 	}
	// 	else
	// 	{
	// 		$this->debug($this->l1,"callInFaucet: Alread in $origin. No need to change to run $command $parameters.");
	// 		$this->core->callFeature($command, $parameters);
	// 	}
	// }

	function getResource($channel)
	{

		$input='';
		if (!isset($this->inputBuffer[$channel])) $this->inputBuffer[$channel]='';
		while (@$recieved=socket_recv($this->clients[$channel], $input, 2048, MSG_DONTWAIT))
		{
			$this->inputBuffer[$channel].=$input;
			$input='';
		}

		if ($recieved===0) $this->clientHasClosed($channel);

		$this->debug($this->l3, __CLASS__.'->'.__FUNCTION__.": Got input \"{$this->inputBuffer[$channel]}\"");

		$result=explode($this->inEOL, $this->inputBuffer[$channel]);
		$this->inputBuffer[$channel]='';

		$this->debug($this->l5, __CLASS__.'->'.__FUNCTION__.": Got input \"{$result[0]}\"");

		$lastLineID=count($result)-1;
		# If we must have the EOL, then we need to make sure that the last line is complete. If it isn't, we need to save it for the next iteration.
		if ($this->expectEOL)
		{
			$this->debug($this->l4, __CLASS__.'->'.__FUNCTION__.": Got input \"{$result[0]}\" id=$lastLineID resolvedValue=\"$result[$lastLineID]\"");

			if ($result[$lastLineID] != '')
			{
				$this->inputBuffer[$channel]=$result[$lastLineID];
				$result[$lastLineID]='';
			}
		}
		else
		{
			if ($lastLineID>0)
			{
				unset($result[$lastLineID]);
			}
		}

		if ($result[0]!=='') return $result;
		else return false;
	}

	function preGet()
	{
		if ($this->isListener) $this->checkForConnections();
		if (!$this->socket) return false; // If we don't have a socket, we don't need to do anything

		$gotSomething=false;

		foreach ($this->clients as $channel=>$value)
		{
			# $this->debug($this->l0, "network->preGet($channel) (".$this->getInstanceName().")");
			if ($output=$this->getResource($channel))
			{
				$this->debug($this->l3, __CLASS__.'->'.__FUNCTION__.": Got input for channel \"$channel\" \"$output[0]\"");
				$this->outFill($output, $channel);
				$this->clearInput($channel);
				$gotSomething=true;
			}
		}

		return $gotSomething;
	}

	function put($data, $channel)
	{ /* Send data out
		Expects an array of strings.
		*/
		if (is_array($data))
		{
			switch ($channel)
			{
				case '_control':
					foreach ($data as $line)
					{
						$parms=$this->core->splitOnceOn(' ', $line);
						$this->control($parms[0], $parms[1]);
					}
					break;
				default:
					if (!isset($this->clients[$channel]))
					{
						$this->debug($this->l2, "SocketServerFaucet->put: Channel $channel doesn't exist. Current clients: ".implode(', ', array_keys($this->clients)));
						break;
					}

					foreach ($data as $line)
					{
						$this->debug($this->l3, __CLASS__.'->'.__FUNCTION__.": Sending line \"$line\"");
						$lineOut="$line{$this->outEOL}";
						if (is_string($line))
						{
							if (socket_write($this->clients[$channel], $lineOut, strlen($lineOut))===false) # TODO is writing the length necessary?
							{
								$this->clientHasClosed($channel);
								return false;
							}
						}
						else $this->debug($this->l2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but struck ".gettype($line)." in the array.");
					}
					break;
			}
		}
		elseif(is_string($data)) $this->debug($this->l2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings but got a string: \"$line\"");
		else $this->debug($this->l2, __CLASS__.'->'.__FUNCTION__.": Expected an array of strings, but got ".gettype($data));
	}

	function control($feature, $value)
	{
		switch ($feature)
		{
			case 'EOL':
				$this->control('inEOL', $value);
				$this->control('outEOL', $value);
				break;
			case 'inEOL':
				switch ($value)
				{
					case '':
						$this->inEOL="";
						break;
					case 'n':
						$this->inEOL="\n";
						break;
					case 'r':
						$this->inEOL="\r";
						break;
					case 'rn':
						$this->inEOL="\r\n";
						break;
				}
				break;
			case 'outEOL':
				switch ($value)
				{
					case '':
						$this->outEOL="";
						break;
					case 'n':
						$this->outEOL="\n";
						break;
					case 'r':
						$this->outEOL="\r";
						break;
					case 'rn':
						$this->outEOL="\r\n";
						break;
				}
				break;
			case 'expectEOL':
				$this->expectEOL=($value);
				break;
			default:
				$this->debug($this->l1, "Control feature $feature not found within ".__CLASS__.". It was called with \"$value\"");
				return false;
				break;
		}
	}
}


$core=core::assert();
$achelNetwork=new NetworkFaucets();
$core->registerModule($achelNetwork);
