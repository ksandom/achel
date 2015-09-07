#!/bin/bash
# Test installing as root

useradd -m -s /bin/zsh testuser
chsh testuser -s /usr/bin/zsh
su - testuser -c runInstall
su - testuser -c "achel --unitTests"
