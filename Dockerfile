FROM ubuntu
MAINTAINER Kevin Sandom

# Install the bare minimum to function.
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y php7.2-cli php7.2-curl curl git wget apt-utils
# TODO finish timezone config

ENV FORCE_UPDATE=1
ADD . /usr/installs/achel

RUN cd /usr/installs/achel && \
  ./install.sh && \
  mkdir -p /usr/achelData/tmp && \
  cd /etc/achel && \
  mv config data /usr/achelData && \
  ln -s /usr/achelData/* . && \
  /etc/achel/repos/achel/automation/dockerInternal/postInstall

CMD automation/dockerInternal/nothingService
