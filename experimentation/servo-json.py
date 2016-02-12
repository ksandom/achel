#!/usr/bin/python -u
'''
Prototype servo control for Achel.
'''

'''
TODO define structure

	Input
		config
		data

	Output
		debug
		error
		state



'''


try:
	import RPi.GPIO as GPIO
except:
	# TODO Catching this and then continuing is at the expense of getting something meaningful on STDERR. Find a better solution.
	# TODO Use new self.state()
	print "{\"state\":\"not running\", \"reason\":\"Could not load GPIO.\"}"
	
import time
import sys
import json

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
		self.pins={}
		self.inputData={}
		
		GPIO.setmode(GPIO.BOARD)
		
		minMS=0.5
		centerMS=1.5
		maxMS=2.5
		
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
		
		self.debug(2, "In range " + str(inMin) + " - " + str(inMax))
		
		
		self.registerPin(7, 0, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(11, 1, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(12, 2, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(13, 3, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(15, 4, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(16, 5, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(18, 6, inMin, inMax, outMin, outMax, outCenter, frequency)
		self.registerPin(22, 7, inMin, inMax, outMin, outMax, outCenter, frequency)

	def registerPin(self, pinID, inputBinding, inMin, inMax, outMin, outMax, outCenter, frequency):
		
		self.pins[pinID] = {
			'pinID':pinID,
			'inputBinding':inputBinding,
			'inMin':inMin,
			'inMax':inMax,
			'outMin':outMin,
			'outCenter':outCenter,
			'outMax':outMax}
		
		self.debug(2, 'Debug: registering pin ' + str(pinID))
		
		# TODO Refactor so that it doesn't stamp on previously written values.
		# TODO add the possibility for a default value.
		self.inputData[inputBinding]=inMin
		
		GPIO.setup(pinID,GPIO.OUT)
		self.pins[pinID]['physicalPin'] = GPIO.PWM(pinID, frequency)
		self.pins[pinID]['physicalPin'].start(outCenter)
	
	def scale(self, value, inMin, inMax, outMin, outMax):
		# Takes a value, checks it's within bounds and scales accordingly.
		
		# Sanity
		if not (inMax > inMin):
			raise ValueError("inMax not greater than inMin")
		if not (outMax > outMin):
			raise ValueError("outMax not greater than outtMin")
		
		# Check bounds
		if value < inMin:
			self.debug(2, "OOB " + str(value) + " < " + str(inMin))
			return outMin
		if value > inMax:
			self.debug(2, "OOB " + str(value) + " > " + str(inMax))
			return outMax
		
		# Derive scales
		inScale=inMax-inMin
		outScale=outMax-outMin

		# Derive final value
		finalValue=(value-inMin)/inScale*outScale+outMin
		
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
		for pin in self.pins:
			self.pins[pin]['physicalPin'].stop()
		
		GPIO.cleanup()


	def oldMain(self):
		# Startup
		
		try:
			print "Debug: Entering loop"
			while True:
				gotInput = self.getRawInput()
				if isinstance(gotInput, (bool, str)):
					if gotInput == 'EOF':
						self.quit("EOF")
						break
					elif gotInput == 'quit':
						self.quit("quit")
						break
				else:
					for pin in self.pins:
						# print self.pins[pin]['inputBinding']
						floater=self.getInput(self.pins[pin]['inputBinding'])
						if isinstance(floater, (float)):
							scaled=self.scale(floater, self.pins[pin]['inMin'], self.pins[pin]['inMax'], self.pins[pin]['outMin'], self.pins[pin]['outMax'])
							self.pins[pin]['physicalPin'].ChangeDutyCycle(scaled)
						else:
							pass
				
				# time.sleep(0.1)
				
			print "Debug: clean exit"

		except KeyboardInterrupt:
			self.quit("Keyboard intrerupt")
	
	def returnData(self, dataType, level, shortMessage, message):
		result={"dataType":dataType, "level":level, "shortMessage":shortMessage, "message":message}
		json.dumps(result)
	
	def debug(self, level, message):
		self.returnData("debug", level, "NA", message)
	
	def state(self, what, why):
		self.returnData("state", "0", what, why)
	
	def error(self, level, what, why):
		self.returnData("error", level, what, why)
	
	def main(self):
		try:
			sys.stdin.readline()
		except KeyboardInterrupt:
			self.state("terminating", "recieved interrupt")
			self.quit("Keyboard intrerupt")
		finally:
			controller.remove_listener(listener)


arb = AchelRealityBridge()
arb.main()
