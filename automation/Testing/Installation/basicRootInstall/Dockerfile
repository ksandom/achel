FROM debian:jessie
RUN apt-get update && apt-get install -y php5-cli git

ENV builton=2015-09-02
ENV preCloneSerial=001
RUN mkdir -p /var/achelRepos && cd /var/achelRepos && git clone https://github.com/ksandom/achel.git

# Install everything
ENV preInstallSerial=001
RUN chmod 755 /var/achelRepos/achel/install.sh
RUN cd /var/achelRepos/achel && /var/achelRepos/achel/install.sh
RUN achel --unitTests
