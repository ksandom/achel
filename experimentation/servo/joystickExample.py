#!/usr/bin/python

# From http://www.pygame.org/wiki/Joystick_analyzer?parent=CookBook
#######################################
# Code coded by Mike Doty
#
# If you want trackball checking, you will
# have to code it yourself.  Sorry!
#
# Oh, and it just grabs the first joystick.
#   Yes, that makes me lazy.
#
# Released February 8, 2008.
#######################################
 
import pygame
from pygame.locals import *
 
class App:
    def __init__(self):
        pygame.init()
 
        # Set up the joystick
        pygame.joystick.init()
 
        self.my_joystick = None
        self.joystick_names = []
 
        # Enumerate joysticks
        for i in range(0, pygame.joystick.get_count()):
            self.joystick_names.append(pygame.joystick.Joystick(i).get_name())
 
        print self.joystick_names
 
        # By default, load the first available joystick.
        if (len(self.joystick_names) > 0):
            self.my_joystick = pygame.joystick.Joystick(0)
            self.my_joystick.init()
 
        max_joy = max(self.my_joystick.get_numaxes(), 
                      self.my_joystick.get_numbuttons(), 
                      self.my_joystick.get_numhats())
 
    # A couple of joystick functions...
    def check_axis(self, p_axis):
        if (self.my_joystick):
            if (p_axis < self.my_joystick.get_numaxes()):
                return self.my_joystick.get_axis(p_axis)
 
        return 0
 
    def check_button(self, p_button):
        if (self.my_joystick):
            if (p_button < self.my_joystick.get_numbuttons()):
                return self.my_joystick.get_button(p_button)
 
        return False
 
    def check_hat(self, p_hat):
        if (self.my_joystick):
            if (p_hat < self.my_joystick.get_numhats()):
                return self.my_joystick.get_hat(p_hat)
 
        return (0, 0)
 
    def main(self):
        while (True):
            #for i in range(0, self.my_joystick.get_numaxes()):
            #    print str(i)+" -> "+str(self.my_joystick.get_axis(i))
                print self.my_joystick.get_axis(0)
 
            #for i in range(0, self.my_joystick.get_numbuttons()):
            #    if (self.my_joystick.get_button(i)):
            #        pygame.draw.circle(self.screen, (0, 0, 200), 
            #                           (20 + (i * 30), 100), 10, 0)
            #    else:
            #        pygame.draw.circle(self.screen, (255, 0, 0), 
            #                           (20 + (i * 30), 100), 10, 0)
 
            #    self.center_text("%d" % i, 20 + (i * 30), 100, (255, 255, 255))
 
            #self.draw_text("POV Hats (%d)" % self.my_joystick.get_numhats(), 
            #               5, 125, (255, 255, 255))
 
            #for i in range(0, self.my_joystick.get_numhats()):
            #    if (self.my_joystick.get_hat(i) != (0, 0)):
            #        pygame.draw.circle(self.screen, (0, 0, 200), 
            #                           (20 + (i * 30), 150), 10, 0)
            #    else:
            #        pygame.draw.circle(self.screen, (255, 0, 0), 
            #                           (20 + (i * 30), 150), 10, 0)
 
            #    self.center_text("%d" % i, 20 + (i * 30), 100, (255, 255, 255))
 
 
    def quit(self):
	pass
 
try:
	app = App()
	app.main()
except KeyboardInterrupt:
	app.quit()
