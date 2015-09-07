#!/bin/bash
# Test a basic user install.

useradd -m -s /bin/bash testuser
su - testuser runInstall
