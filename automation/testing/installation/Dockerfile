# STATUS: untested!

FROM debian:jessie
RUN apt-get update && apt-get install -y php5-cli git zsh

ENV builton=2015-10-15
ENV preCloneSerial=000

# Prep for install
ENV preInstallSerial=000
ADD runInstall /usr/bin/runInstall
ADD tests /var/tests
RUN chmod 755 /var/tests/* /usr/bin/runInstall
