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


pin = 7
min = 3
center = 7.5
max = 11.3

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
			time.sleep(0.2)
			continue
		
		print line
		
		try:
			floater=float(line)
		except ValueError:
			time.sleep(0.2)
			continue
		
		if floater < min:
			print "OOB < min"
			continue
		
		if floater > max:
			print "OOB > max"
			continue
		
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

