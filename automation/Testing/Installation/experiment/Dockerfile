# STATUS: untested!

FROM debian:jessie
RUN apt-get update && apt-get install -y php5-cli git

ENV builton=2015-09-03
ENV preCloneSerial=000

# Prep for install
ENV preInstallSerial=000
RUN useradd -m -s /bin/bash testuser
ADD runInstall /usr/bin/runInstall
