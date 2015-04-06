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
pin = 7
outMin = 3.0
outCenter = 7.5
outMax = 11.0

inMin=0
try:
	inMax=float(sys.argv[1])
except:
	inMax=100



class AchelRealityBridge:
	def __init__(self):
		pass
	
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
		p.stop()
		GPIO.cleanup()


	def main(self):
		# Startup
		GPIO.setmode(GPIO.BOARD)
		GPIO.setup(pin,GPIO.OUT)

		p = GPIO.PWM(pin,50)
		p.start(outCenter)

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
				
				try:
					floater=float(line)
				except ValueError:
					time.sleep(0.2)
					continue
				
				floater=self.scale(floater, 0, inMax, outMin, outMax)
				
				print "input=" + line + " scaled="+str(floater)
				p.ChangeDutyCycle(floater)
				time.sleep(0.5)
				
				if line == "quit":
					print "requested quit"
					break;
			print "clean exit"

		except KeyboardInterrupt:
			self.quit

arb = AchelRealityBridge()
arb.main()
