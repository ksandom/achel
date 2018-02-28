# A full, clean and light Achel install.
FROM alpine:latest
MAINTAINER Kevin Sandom

RUN apk update && \
    apk add php7 php7-curl php7-json && \
    apk add bash && \
    apk add git

ADD . /usr/installs/achel

RUN cd /usr/installs/achel && ./install.sh
    
