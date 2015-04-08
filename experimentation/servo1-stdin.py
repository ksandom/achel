#!/usr/bin/python

import RPi.GPIO as GPIO
import time
import sys

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
		outMin = 3.0
		outCenter = 7.5
		outMax = 11.0
		
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
		
		print "Debug: Got range " + str(inMin) + " - " + str(inMax)
		
		
		self.registerPin(7, 0, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(11, 1, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(12, 2, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(13, 3, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(15, 4, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(16, 5, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(18, 6, inMin, inMax, outMin, outMax, outCenter)
		self.registerPin(22, 7, inMin, inMax, outMin, outMax, outCenter)

	def registerPin(self, pinID, inputBinding, inMin, inMax, outMin, outMax, outCenter):
		
		self.pins[pinID] = {
			'pinID':pinID,
			'inputBinding':inputBinding,
			'inMin':inMin,
			'inMax':inMax,
			'outMin':outMin,
			'outCenter':outCenter,
			'outMax':outMax}
		
		print 'Debug: registering pin ' + str(pinID)
		
		# TODO Refactor so that it doesn't stamp on previously written values.
		# TODO add the possibility for a default value.
		self.inputData[inputBinding]=inMin
		
		GPIO.setup(pinID,GPIO.OUT)
		self.pins[pinID]['physicalPin'] = GPIO.PWM(pinID, 50)
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
			print "OOB " + str(value) + " < " + str(inMin)
			return outMin
		if value > inMax:
			print "OOB " + str(value) + " > " + str(inMax)
			return outMax
		
		# Derive scales
		inScale=inMax-inMin
		outScale=outMax-outMin

		# Derive final value
		finalValue=(value-inMin)/inScale*outScale+outMin
		print str(finalValue) +"=("+str(value) +"-"+ str(inMin) +")/"+ str(inScale)+ "*" + str(outScale)+ "+" +str(outMin)
		
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
		print "Debug: "+message
		for pin in self.pins:
			self.pins[pin]['physicalPin'].stop()
		
		GPIO.cleanup()


	def main(self):
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

arb = AchelRealityBridge()
arb.main()
