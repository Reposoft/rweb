FROM php:5.6@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143

RUN set -e; \
  apt-get update; \
  mkdir -p /usr/share/man/man1; \
  apt-get install --no-install-recommends -y ant zip

RUN set -e; \
  curl -sL https://deb.nodesource.com/setup_12.x | bash -; \
  apt-get install -y nodejs

RUN mkdir /.npm && chown -R 1000:0 "/.npm"
USER 1000
