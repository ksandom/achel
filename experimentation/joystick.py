#!/usr/bin/python

import time
from pygame import joystick
j = joystick.Joystick(0)
j.init()

if not j.get_init():
	j.quit()
	print "Joystick not initialised"
else:
	try:
		while True:
			print "Got here"
			print j.get_axis(0)
			time.sleep(1)
	except KeyboardInterrupt:
		j.quit()
