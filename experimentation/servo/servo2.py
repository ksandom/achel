#!/usr/bin/python

import RPi.GPIO as GPIO
import time

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


pinList = [7,11]
min = 3
center = 7.5
max = 11.3

GPIO.setmode(GPIO.BOARD)

def setupPins(pinList, startPosition):
	pins={}
	for pin in pinList:
		GPIO.setup(pin,GPIO.OUT)
		pins[pin]=GPIO.PWM(pin,50)
		pins[pin].start(startPosition)
	
	return pins

def setPins(pins, position):
	for index,pin in pins:
		pin.ChangeDutyCycle(position)
		
def iterate(pinList, min, center, max):
	try:
		pins = setupPins(pinList, center)
		
		while True:
			setPins(pins, max)
			time.sleep(1)
			setPins(pins, min)
			time.sleep(1)
			setPins(pins, center)
			time.sleep(1)
	
	except KeyboardInterrupt:
		p.stop()
		GPIO.cleanup()

	except RuntimeWarning:
		p.stop()
		GPIO.cleanup()

iterate (pinList, min, center, max)
	
