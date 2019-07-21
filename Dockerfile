FROM ubuntu
MAINTAINER Kevin Sandom

# Install the bare minimum to function.
ENV DEBIAN_FRONTEND=noninteractive
RUN apt update && apt install -y php7.2-cli php7.2-curl curl git wget
# TODO finish timezone config

ADD . /usr/installs/achel

RUN cd /usr/installs/achel && \
  ./install.sh && \
  mkdir -p /usr/achelData && \
  cd /etc/achel && \
  mv config data /usr/achelData && \
  ln -s /usr/achelData/* . && \
  /etc/achel/repos/achel/automation/dockerInternal/postInstall

CMD automation/dockerInternal/nothingService
