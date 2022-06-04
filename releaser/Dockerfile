FROM php:8.1.6-alpine3.15@sha256:ad4a4d0c78da5867ba5ceb00d6d4275559560fb01deabb75bb1b7e162d49e98a

RUN apk add --no-cache \
  bash \
  apache-ant \
  zip \
  nodejs npm

RUN mkdir /.npm && chown -R 1000:0 "/.npm"
USER 1000
