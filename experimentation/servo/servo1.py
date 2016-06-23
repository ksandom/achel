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


pin = 7
min = 3
center = 7.5
max = 11.3

GPIO.setmode(GPIO.BOARD)

GPIO.setup(pin,GPIO.OUT)

p = GPIO.PWM(pin,50)
p.start(center)

try:
	while True:
		p.ChangeDutyCycle(center)
		time.sleep(1)
		p.ChangeDutyCycle(max)
		time.sleep(1)
		p.ChangeDutyCycle(min)
		time.sleep(1)

except KeyboardInterrupt:
	p.stop()
	GPIO.cleanup()

