<?php
# Copyright (c) 2013-2018, Kevin Sandom under the GPL License. See LICENSE for full details.

define('minutes', 60);
define('hours', 3600);
define('days', 86400);
define('weeks', 604800);
define('months', 2592000);
define('years', 31536000);
define('fuzzyTimeThreshold', 5*years);

class TimeThing extends Module
{
	private $track=null;
	private $store=null;
	private $codes=false;
	private $throttle=0;
	private $throttlePace=0;

	function __construct()
	{
		parent::__construct('TimeThing');
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('now'), 'now', 'Put the current time in seconds into a store variable. --now=Category,variableName', array('time'));
				$this->core->registerFeature($this, array('microNow'), 'microNow', 'Put a decimal seconds offset into a store variable. --now=Category,variableName', array('time'));
				$this->core->registerFeature($this, array('timeDiff'), 'timeDiff', 'Put the difference of two times into a store variable. --timeDiff=Category,variableName,inputTime1,inputTime2 . inputTime 1 and 2 are time represented in seconds.', array('help'));
				$this->core->registerFeature($this, array('fuzzyTime'), 'fuzzyTime', 'Put the fuzzyTime (eg "5 hours") into a store variable. --fuzzyTime=Category,variableName,inputTime[,maxUnit] . inputTime is time represented in seconds. maxUnit', array('help'));
				$this->core->registerFeature($this, array('fullTimeStamp'), 'fullTimeStamp', 'Put a full timestamp (eg "2013-04-17--20:12:10") into a store variable. --fullTimeStamp=Category,variableName,[inputTime][,format] . inputTime is time represented in seconds, and will default to now if omitted. format is defined in http://php.net/manual/en/function.date.php and defaults to ~!Settings,timestampFormat!~.', array('help'));
				$this->core->registerFeature($this, array('strToTime'), 'strToTime', "Uses PHP's strtotime() function to get a timestamp that is useable by the other functions. --strToTime=Category,variableName,string[,baseTime]. string is something like \"yesterday\" or \"-1 day\".", array('help'));
				$this->core->registerFeature($this, array('throttle'), 'throttle', "Will only run a feature if it hasn't been run in the last x milliseconds --throttle=x, . This is useful for periodically running something in a loop such as showing the progress to a user, without slowing down the process too much. Note that the comma at the end is important.", array('help','time'));
				$this->core->registerFeature($this, array('throttleBetween'), 'throttleBetween', "Will only run a feature if it hasn't been run in the last x milliseconds --throttleBetween=x1,x2,increment, . This is basically the same as --throttle, except the trottling changes with each increment. The intention of this is scale the throttling gracefully so that long running tasks can become more efficient.", array('help','time'));

				# TODO This is probably better in config. Then we could do some funky things with configuring fuzzy timestamps.
				$this->core->set('Time', 'fuzzyTimeThreshold', fuzzyTimeThreshold);
				break;
			case 'followup':
				break;
			case 'last':
				break;
			case 'now':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->core->set($parms[0], $parms[1], $this->now());
				break;
			case 'microNow':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->core->set($parms[0], $parms[1], $this->now(true));
				break;
			case 'timeDiff':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4, true);
				$this->core->set($parms[0], $parms[1], $this->timeDiff($parms[2], $parms[3]));
				break;
			case 'fuzzyTime':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 3, true);
				$this->core->set($parms[0], $parms[1], $this->fuzzyTime($parms[2], $parms[3]));
				break;
			case 'fullTimeStamp':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 2, true);
				if ($parms[3]) $this->core->set($parms[0], $parms[1], $this->fullTimeStamp($parms[2], $parms[3]));
				else $this->core->set($parms[0], $parms[1], $this->fullTimeStamp($parms[2]));
				break;
			case 'strToTime':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 3, true);
				if ($parms[3]) $this->core->set($parms[0], $parms[1], strToTime($parms[2], $parms[3]));
				else $this->core->set($parms[0], $parms[1], strToTime($parms[2]));
				break;
			case 'throttle':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 2, 2, true);
				$this->throttle($parms[0], $parms[1]);
				break;
			case 'throttleBetween':
				$parms=$this->core->interpretParms($this->core->get('Global', $event), 4, 4, true);
				$this->throttleBetween($parms[0], $parms[1], $parms[2], $parms[3]);
				break;
			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function now($microTime=false)
	{
		if ($microTime) return microtime(true);
		else return time();
	}

	function timeDiff($inputTime1, $inputTime2)
	{
		if ($inputTime1=='' or $inputTime2=='') {
			$this->core->complain($this, "At least one of inputTime1($inputTime1) or inputTime2($inputTime2) is empty.", "timeDiff");

			if ($inputTime1=='') return "inputTime1 not set/empty.";
			if ($inputTime2=='') return "inputTime2 not set/empty.";
		}

		return $inputTime2-$inputTime1;
	}

	function fuzzyTime($inputTime, $maxUnit='')
	{
		$accuracy=1;

		if ($inputTime>fuzzyTimeThreshold)
		{
			return $this->fullTimeStamp($inputTime);
		}

		if ($inputTime<minutes or $maxUnit=='seconds')
		{
			$unit='second';
			$value=$inputTime;
		}
		else
		{
			if ($inputTime<hours or $maxUnit=='minutes')
			{
				$unit='minute';
				$value=round($inputTime/minutes, $accuracy);
			}
			else
			{
				if ($inputTime<days or $maxUnit=='hours')
				{
					$unit='hour';
					$value=round($inputTime/hours, $accuracy);
				}
				else
				{
					if ($inputTime<weeks or $maxUnit=='days')
					{
						$unit='day';
						$value=round($inputTime/days, $accuracy);
					}
					else
					{
						if ($inputTime<months or $maxUnit=='weeks')
						{
							$unit='week';
							$value=round($inputTime/weeks, $accuracy);
						}
						else
						{
							if ($inputTime<years or $maxUnit=='months')
							{
								$unit='month';
								$value=round($inputTime/months, $accuracy);
							}
							else
							{
								$unit='year';
								$value=round($inputTime/years, $accuracy);
							}
						}
					}
				}
			}
		}

		// Almost done.
		$output="$value $unit";

		// Cater to plurals.
		if (intval($value)!=1 or strpos($value, '.'))
		{
			$output.='s';
		}

		return $output;
	}

	function fullTimeStamp($inputTime, $format='')
	{
		if (!$format)
		{
			$format=$this->core->get('Settings','timestampFormat');
			$this->core->debug(1, "format: $format");
		}
		else
		{
			$this->core->debug(1, "format: $format (default)");
		}

		$time=($inputTime)?$inputTime:$this->now();
		if (!is_numeric($inputTime))
		{
			$error="inputTime($inputTime) does not appear to be a number.";
			$this->core->complain($this, $error, "fullTimeStamp");
			return $error;
		}

		return date($format, $time);
	}

	function throttle($milliseconds, $feature)
	{
		$now=microtime(true);

		if ($now-$this->throttle > $milliseconds/1000)
		{
			$this->core->callFeature($feature, '');
			$this->throttle=$now;
		}
	}

	function throttleBetween($millisecondsFrom, $millisecondsTo, $increment, $feature)
	{
		$now=microtime(true);

		# Derive orientation and related stuff
		if ($millisecondsFrom<$millisecondsTo)
		{ # Forwards
			$incrementor=$increment;
			if ($this->throttlePace<$millisecondsFrom) $this->throttlePace=$millisecondsFrom;
			if ($this->throttlePace>$millisecondsTo) $this->throttlePace=$millisecondsTo;
		}
		else
		{ # Backwards
			$incrementor=$increment*-1;
			if ($this->throttlePace>$millisecondsFrom) $this->throttlePace=$millisecondsFrom;
			if ($this->throttlePace<$millisecondsTo) $this->throttlePace=$millisecondsTo;
		}

		$now=microtime(true);

		if ($now-$this->throttle > $this->throttlePace/1000)
		{
			$this->core->callFeature($feature, '');
			$this->throttle=$now;
			$this->throttlePace+=$incrementor;
		}
	}
}

$core=core::assert();
$timeThing=new TimeThing();
$core->registerModule($timeThing);

?>
