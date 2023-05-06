<?php
# Copyright (c) 2013-2023, Kevin Sandom under the GPL License. See LICENSE for full details.

class Maths extends Module
{
	private $dataDir=null;

	function __construct()
	{
		parent::__construct('Maths');
	}

	function event($event)
	{
		switch ($event)
		{
			case 'init':
				$this->core->registerFeature($this, array('basicMaths'), 'basicMaths', 'Apply a mathematical operator on two numbers and put the results into a store variable. --basicMaths=Category,variableName,value1,operator,value2 . Current accepted operators are +, -, *, /, %, ^, sr, /!, /<, />. sr is square root. /! is assume false on divide by 0. /< is assume the left value on divide by zero, and /> assumes right.', array('maths','calculations'));
				$this->core->registerFeature($this, array('round'), 'round', 'Round a value to a certain precision. --round=Category,variableName,value,precision . precision  is how many decimal places to round to.', array('maths'));
				$this->core->registerFeature($this, array('absolute'), 'absolute', 'Give the absolute value of a value (ie input can be positive or negative. Output will always be the positive version of that input.). --absolute=Category,variableName,value .', array('maths'));
				break;
			case 'followup':
				break;
			case 'last':
				break;

			case 'basicMaths':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event));
				if ($this->core->requireNumParms($this, 5, $event, $originalParms, $parms))
				{
					$returnValue=$this->basicMaths($parms[2], $parms[3], $parms[4]);
					$this->core->set($parms[0], $parms[1], $returnValue);
				}
				break;
			case 'round':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 4, 4);
				if (!is_numeric($parms[2]))
				{
					$this->debug(1, "round: Was expecting a number. Got \"{$parms[2]}\"");
					break;
				}
				$this->core->set($parms[0], $parms[1], round($parms[2], $parms[3]));
				break;
			case 'absolute':
				$parms=$this->core->interpretParms($originalParms=$this->core->get('Global', $event), 3, 3);
				if (!is_numeric($parms[2]))
				{
					$this->debug(1, "absolute: Was expecting a number. Got \"{$parms[2]}\"");
					break;
				}
				$this->core->set($parms[0], $parms[1], abs($parms[2]));
				break;

			default:
				$this->core->complain($this, 'Unknown event', $event);
				break;
		}
	}

	function basicMaths($value1, $operator, $value2)
	{
		switch ($operator)
		{
			case '*': # Multiply
				return $this->sanitise($value1)*$this->sanitise($value2);
				break;
			case '/': # Divide - Complain loudly on divide by 0.
				if ( $this->sanitise($value2)!=0) return $this->sanitise($value1)/$this->sanitise($value2);
				else
				{
					$this->debug(1, "Divide by zero in $value1,$operator,$value2 . Returning false.");
					return false;
				}
				break;
			case '/!': # Divide - Assume false on divide by 0.
				if ( $this->sanitise($value2)!=0) return $this->sanitise($value1)/$this->sanitise($value2);
				else
				{
					$this->debug(3, "Divide by zero in $value1,$operator,$value2 . Returning false since /! was specified.");
					return false;
				}
				break;
			case '/>': # Divide - Assume value2 (0) on divide by 0.
				if ( $this->sanitise($value2)!=0) return $this->sanitise($value1)/$this->sanitise($value2);
				else
				{
					$this->debug(3, "Divide by zero in $value1,$operator,$value2 . Assuming value2($value2) since the operator was />.");
					return $this->sanitise($value2);
				}
				break;
			case '/<': # Divide - Assume value1 on divide by 0.
				if ($value2!=0) return $this->sanitise($value1)/$this->sanitise($value2);
				else
				{
					$this->debug(3, "Divide by zero in $value1,$operator,$value2 . Assuming value1($value1) since the operator was /<.");
					return $this->sanitise($value1);
				}
				break;
			case '+': # Add
				return $this->sanitise($value1)+$this->sanitise($value2);
				break;
			case '-': # Subtract
				return $this->sanitise($value1)-$this->sanitise($value2);
				break;
			case '%': # Modulus
				$cleanedValue=$this->sanitise($value1);
				$decimal=$this->preserveDecimal($cleanedValue);
				return $cleanedValue%$this->sanitise($value2)+$decimal;
				break;
			case '^': # Exponent
				return pow($this->sanitise($value1), $this->sanitise($value2));
			case 'sr': # Square Root
				return sqrt($value1);
				break;
		}
	}

	private function preserveDecimal($value)
	{
		return $value-round($value, 0);
	}

	function sanitise($value)
	{
            if (is_numeric($value)) return $value;
            elseIf ($value=='') return 0;
            else
            {
                $this->core->complain($this, "I don't know how to handle this as a number. Going to see if PHP can figure it out.", $value);
                return $value;
            }
	}
}

$core=core::assert();
$maths=new Maths();
$core->registerModule($maths);

?>
