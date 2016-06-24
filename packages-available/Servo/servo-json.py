#!/usr/bin/python -u
'''
Prototype servo control for Achel.
'''

'''
TODO define structure

	Input
		config
		data
		ping

	Output
		debug
		error
		state
		pong



'''

import time
import sys
import json
import pprint

try:
	import RPi.GPIO as GPIO
	print "{\"command\":\"debug\",\"level\":\"4\",\"shortMessage\":\"gpioLoaded\", \"message\":\"GPIO was successfully loaded.\"}"
except:
	# TODO Catching this and then continuing is at the expense of getting something meaningful on STDERR. Find a better solution.
	# TODO Use new self.state()
	print "{\"command\":\"error\",\"level\":\"0\",\"shortMessage\":\"not running\", \"message\":\"Could not load GPIO.\"}"
	# arb.error("0", "no GPIO", "GPIO could not be loaded. No GPIO operations will work. At this point you are in testing only mode.")
	

'''
Pins
	7
	11
	12
	13
	15
	16
	18
	22
'''

# Set some defaults

class AchelRealityBridge:
	def __init__(self):
		self.nutered=False
		self.setDefaultValues()
		self.configureGPIO()
	
	def configureGPIO(self):
		try:
			GPIO.setmode(GPIO.BOARD)
			self.gpioStarted=True
			self.state("gpioStarted", "GPIO successfully started.")
		except Exception as e:
			self.exception(e, 'configureGPIO')
			self.state("noGPIO", "could not start GPIO.")
			self.gpioStarted=False
	
	def setDefaultValues(self):
		self.pins={}
		self.inputData={}
		
		
		minMS=0.6
		centerMS=1.5
		maxMS=2.2
		
		# Get a frequency if we have it.
		try:
			frequency=int(sys.argv[3])
		except:
			frequency=15
		
		# Calculate timings
		divisor=1000/frequency
		
		outMin = minMS/divisor*100
		outCenter = centerMS/divisor*100
		outMax = maxMS/divisor*100
		
		self.debug(2, "frequency " + str(frequency))
		self.debug(2, "Out range " + str(outMin) + " - " + str(outCenter) + " - " + str(outMax))
		
		# Get a max value if we have it.
		try:
			inMax=float(sys.argv[1])
		except:
			inMax=100
		
		# Get a min value if we have it and move the max value out of the way if it's relevant
		try:
			tmp=float(sys.argv[2])
			inMin=inMax
			inMax=tmp
		except:
			inMin=0
		
		self.servoInMin=inMin
		self.servoInMax=inMax
		self.servoOutMin=outMin
		self.servoOutMax=outMax
		self.servoOutCenter=outCenter
		self.servoFrequency=frequency
		
		self.debug(2, "In range " + str(inMin) + " - " + str(inMax))
	
	def registerAllPins(self):
		try:
			self.registerPin(7, "0", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(11, "1", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(12, "2", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(13, "3", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(15, "4", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(16, "5", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(18, "6", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
			self.registerPin(22, "7", self.servoInMin, self.servoInMax, self.servoOutMin, self.servoOutMax, self.servoOutCenter, self.servoFrequency)
		except Exception as e:
			self.exception(e, "registerAllPins.")
	
	def oldInit(self):
		
		self.configureGPIO()
		self.setDefaultValues()
		self.registerAllPins()
		
		

	def registerPin(self, pinID, inputBinding, inMin, inMax, outMin, outMax, outCenter, frequency):
		
		try:
			strPinID=str(inputBinding)
			self.pins[strPinID] = {
				'pinID':pinID,
				'inputBinding':inputBinding,
				'inMin':inMin,
				'inMax':inMax,
				'outMin':outMin,
				'outCenter':outCenter,
				'outMax':outMax}
			
			self.debug(2, 'Debug: registering pin ' + str(pinID) +' as '+str(inputBinding))
			
			# TODO Refactor so that it doesn't stamp on previously written values.
			# TODO add the possibility for a default value.
			self.inputData[inputBinding]=inMin
			
			GPIO.setup(pinID,GPIO.OUT)
			self.pins[strPinID]['physicalPin'] = GPIO.PWM(pinID, frequency)
			self.pins[strPinID]['physicalPin'].start(outCenter)
			
		except Exception as e:
			self.exception(e, "registerPin. PinID="+str(pinID)+" inputBinding="+str(inputBinding))
	
	def scale(self, value, inMin, inMax, outMin, outMax):
		# Takes a value, checks it's within bounds and scales accordingly.
		
		# Sanity
		if not (inMax > inMin):
			raise ValueError("inMax not greater than inMin")
		if not (outMax > outMin):
			raise ValueError("outMax not greater than outtMin")
		
		# Check bounds
		sanitisedValue=float(value)
		if sanitisedValue < float(inMin):
			self.debug(2, "OOB " + str(value) + " < " + str(inMin))
			return outMin
		if sanitisedValue > float(inMax):
			self.debug(2, "OOB " + str(value) + " > " + str(inMax))
			return outMax
		
		# Derive scales
		inScale=inMax-inMin
		outScale=outMax-outMin

		# Derive final value
		finalValue=(sanitisedValue-inMin)/inScale*outScale+outMin
		
		debugging=str(finalValue) +"=("+str(value) +"-"+ str(inMin) +")/"+ str(inScale)+ "*" + str(outScale)+ "+" +str(outMin)
		self.debug(2, debugging)
		
		return finalValue
	
	def getRawInput(self):
		line = "nothing"
		try:
			line = raw_input()
		except EOFError:
			return "EOF"
		
		if line == '':
			print "no input"
		elif line == "quit":
			return "quit";
		
		self.inputData = line.split(',')
	
	def getInput(self, inputID):
		try:
			value=self.inputData[inputID]
		except IndexError:
			return False
		
		try:
			floater=float(value)
			return floater
		except ValueError:
			return False

	def quit(self, message):
		self.debug(2, message)
		try:
			for pin in self.pins:
				self.pins[pin]['physicalPin'].stop()
			
			GPIO.cleanup()
		except Exception as e:
			self.error(1, 'no GPIO', 'Could not cleanup GPIO. Not available?')

	def setPins(self, data):
		changeCount=0
		failCount=0
		nuteredCount=0
		
		for pin in data:
			try:
				if not (self.nutered):
					if (self.setPin(str(pin), data[pin])):
						changeCount=changeCount+1
					else:
						failCount=failCount+1
				else:
					self.debug(1, "would write pin "+pin+", but currently nutered.")
					nuteredCount=nuteredCount+1
			except Exception as e:
				self.exception(e, "setPins. Pin="+pin+" Value="+data[pin])
				failCount=failCount+1
		
		self.debug(3, "Set "+str(changeCount)+" pins. Nutered "+str(nuteredCount)+" pins. Failed to set "+str(failCount)+" pins.")
	
	def setPin(self, pin, value):
		self.debug("3", "Got data")
		# TODO remove this: pprint.pprint (self.pins)
		try:
			inMin=self.pins[pin]['inMin']
			inMax=self.pins[pin]['inMax']
			outMin=self.pins[pin]['outMin']
			outMax=self.pins[pin]['outMax']
			scaled=self.scale(value, inMin, inMax, outMin, outMax)
			self.pins[pin]['physicalPin'].ChangeDutyCycle(scaled)
			return True
		except Exception as e:
			self.exception(e, "setPin")
			return False
		
	
	def isGpioStarted(self):
		if (self.gpioStarted):
			self.returnData("gpioState", "0", "gpioStarted", "NA")
		else:
			self.returnData("gpioState", "0", "gpioNotStarted", "NA")
	
	def nuter(self, value):
		self.nutered = (value == "true")
		if (self.nutered):
			resultValue="true"
		else:
			resultValue="false"
		
		self.debug(2, "Set nutered to "+resultValue+"("+value+").")
	
	def processLine(self, line):
		# Get data from line
		# if (line == ""):
		#	return False
		
		sys.stderr.write(" Input: \""+line+"\"\n")
		
		try:
			data=json.loads(line)
			# Work out what do do with it
			if (data['command'] == "ping"):
				self.returnData('pong', "0", "", "Returned from requested ping.")
			elif (data['command'] == "setAllGenericServos"):
				self.registerAllPins()
				self.debug(2, "Set all pins to generic PWM based servos.")
			elif (data['command'] == "setData"):
				self.setPins(data['data'])
			elif (data['command'] == "isGpioStarted"):
				self.isGpioStarted()
			elif (data['command'] == "nuter"):
				self.nuter(data['message'])
			else:
				self.debug(0, "Unknown command \""+data['command']+"\"")
			
		except ValueError:
			self.error(1, "notJson", "Recieved data was not decodable as json.")
			sys.stderr.write("Not json?: \""+line+"\"\n")
		except KeyError:
			self.error(1, "missing command", "Json was recieved, but command was not in it. Line=\""+line+"\"")
		except TypeError:
			# TODO Happening inside data[]?
			self.error(1, "typeError", "The decoded json does not appear to be a dict.")
			sys.stderr.write("Unexpected json structure: \""+line+"\"\n")
	
	def returnData(self, command, level, shortMessage, message):
		result={"command":command, "level":level, "shortMessage":shortMessage, "message":message}
		output=json.dumps(result)
		print output
		sys.stderr.write("Output: "+output+"\n")
	
	def debug(self, level, message):
		self.returnData("debug", level, "NA", message)
	
	def state(self, what, why):
		self.returnData("state", "0", what, why)
	
	def error(self, level, what, why):
		self.returnData("error", level, what, why)
		sys.stderr.write("Error: Level="+str(level)+" What=\""+what+"\" Why=\""+why+"\"\n")
	
	def exception(self, e, context='NA'):
		template = "An exception of type {0} occured. Arguments:\n{1!r}"
		message = template.format(type(e).__name__, e.args)
		self.returnData("exception", "0", context, message)
	
	def main(self):
		try:
			while True:
				self.processLine(sys.stdin.readline())
		except IOError:
			pass
		except KeyboardInterrupt:
			self.state("terminating", "recieved interrupt")
			self.quit("Keyboard intrerupt")
		finally:
			pass


arb = AchelRealityBridge()
arb.main()
