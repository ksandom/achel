#!/usr/bin/python
"""
This is for testing json based IPC.
"""

import json
import sys

class Pinger:
	def returnData(self, command, level, shortMessage, message):
		result={"command":command, "level":level, "shortMessage":shortMessage, "message":message}
		print json.dumps(result)
	
	def processLine(self, line):
		try:
			data=json.loads(line)
		except:
			self.returnData("error", "0", "json", "Could not decode the json.")
		
		try:
			command=data["command"]
			message=data["message"]
		except:
			command="nodata"
		
		if command=="nodata":
			self.returnData("error", "0", "data", "Missing expected data. Expecting command and message.")
		elif command=="ping":
			self.returnData("pong", "0", "pong", message+" - pong")
	
	def main(self):
		try:
			while True:
				self.processLine(sys.stdin.readline())
		except KeyboardInterrupt:
			self.state("terminating", "recieved interrupt")
			self.quit("Keyboard intrerupt")
		finally:
			pass

p=Pinger()
p.main()
