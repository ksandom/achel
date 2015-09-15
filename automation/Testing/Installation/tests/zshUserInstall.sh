#!/bin/bash
# Test installing as root

useradd -m -s /bin/zsh testuser
chsh testuser -s /usr/bin/zsh
su - testuser -c "/usr/bin/runInstall"
su - testuser -l -c "achel --unitTests"
