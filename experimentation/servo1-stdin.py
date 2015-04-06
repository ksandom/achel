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
		GPIO.setmode(GPIO.BOARD)
		outMin = 3.0
		outCenter = 7.5
		outMax = 11.0
		
		inMin=0
		try:
			inMax=float(sys.argv[1])
		except:
			inMax=100
		
		self.registerPin(7, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(11, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(12, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(13, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(15, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(16, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(18, 0, 0, inMax, outMin, outMax, outCenter)
		self.registerPin(22, 0, 0, inMax, outMin, outMax, outCenter)

	def registerPin(self, pinID, inputBinding, inMin, inMax, outMin, outMax, outCenter):
		
		self.pins[pinID] = {
			'pinID':pinID,
			'inputBinding':inputBinding,
			'inMin':inMin,
			'inMax':inMax,
			'outMin':outMin,
			'outCenter':outCenter,
			'outMax':outMax}
		
		print 'registering pin ' + str(pinID)
		
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

	def quit(self):
		for pin in self.pins:
			pin['physicalPin'].stop()
		
		GPIO.cleanup()


	def main(self):
		# Startup

		try:
			print "Entering loop"
			# for line in sys.stdin.readline():
			while True:
				try:
					line = raw_input()
				except EOFError:
					print "EOF"
					break
					#time.sleep (0.5)
					#continue
					#line=''
				
				if line == '':
					print "no input"
					time.sleep(0.5)
					continue
				if line == "quit":
					print "requested quit"
					break;
				
				try:
					floater=float(line)
				except ValueError:
					time.sleep(0.2)
					continue
				
				
				for pin in self.pins:
					scaled=self.scale(floater, 0, self.pins[pin]['inMax'], self.pins[pin]['outMin'], self.pins[pin]['outMax'])
					
					print str(pin) + " " + "input=" + line + " scaled="+str(scaled)
					self.pins[pin]['physicalPin'].ChangeDutyCycle(scaled)
				
				time.sleep(0.5)
				
			print "clean exit"

		except KeyboardInterrupt:
			self.quit

arb = AchelRealityBridge()
arb.main()
