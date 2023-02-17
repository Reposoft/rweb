FROM php:8.1.16-alpine3.17@sha256:e69f7398a9830d9d08c23eaed7a59e989ee69d80bbcfbf991c819c8ddc2fcbdb

RUN apk add --no-cache \
  bash \
  apache-ant \
  zip \
  nodejs npm

RUN mkdir /.npm && chown -R 1000:0 "/.npm"
USER 1000
