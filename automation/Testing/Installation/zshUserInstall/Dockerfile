# STATUS: untested!

FROM debian:jessie
RUN apt-get update && apt-get install -y php5-cli git zsh

ENV builton=2015-09-03
ENV preCloneSerial=000
RUN mkdir -p /var/achelRepos && cd /var/achelRepos && git clone https://github.com/ksandom/achel.git

# Prep for install
ENV preInstallSerial=000
RUN chmod 755 /var/achelRepos/achel/install.sh
RUN useradd -m -s /bin/zsh testuser

# Do the install as testuser
RUN cd /var/achelRepos/achel && su - testuser -c /var/achelRepos/achel/install.sh
RUN su - testuser -c achel --unitTests
