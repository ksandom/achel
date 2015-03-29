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

def scale(value, inMin, inMax, outMin, outMax):
	# Takes a value, checks it's within bounds and scales accordingly.
	
	# Sanity
	if not (inMax > inMin):
		raise ValueError("inMax not greater than inMin")
	if not (outMax > outMin):
		raise ValueError("outMax not greater than outtMin")
	
	# Check bounds
	if (value<inMin):
		print "OOB " + str(value) + " < " + str(inMin)
		return outMin
	if (value > outMax):
		print "OOB " + str(value)+str(type(value)) + " > " + str(inMax)+str(type(inMax))
		return outMax
	
	# Derive scales
	inScale=inMax-inMin
	outScale=outMax-outMin

	# Derive final value
	finalValue=(value-inMin)/inScale*outScale+outMin
	print str(finalValue) +"=("+str(value) +"-"+ str(inMin) +")/"+ str(inScale)+ "*" + str(outScale)+ "+" +str(outMin)
	
	return finalValue


pin = 7
min = 3.0
center = 7.5
max = 11.0

GPIO.setmode(GPIO.BOARD)

GPIO.setup(pin,GPIO.OUT)

p = GPIO.PWM(pin,50)
p.start(center)

try:
	print "Entering loop"
	# for line in sys.stdin.readline():
	while True:
		try:
			line = raw_input()
		except EOFError:
			print "EOF"
			# break
			time.sleep (0.5)
			continue
			line=''
		
		if line == '':
			print "no input"
			time.sleep(0.5)
			continue
		
		try:
			floater=float(line)
		except ValueError:
			time.sleep(0.2)
			continue
		
		floater=scale(floater, 0, 1, min, max)
		#if floater < min:
		#	print "OOB < min"
		#	continue
		
		#if floater > max:
		#	print "OOB > max"
		#	continue
		
		print "input=" + line + " scaled="+str(floater)
		p.ChangeDutyCycle(floater)
		time.sleep(0.5)
		
		if line == "quit":
			print "requested quit"
			break;
	print "clean exit"
	p.stop()
	GPIO.cleanup()
	

except KeyboardInterrupt:
	p.stop()
	GPIO.cleanup()

