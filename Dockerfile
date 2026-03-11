FROM nginx:stable-alpine
LABEL org.opencontainers.image.source="https://github.com/to236663/inkseek"


RUN rm -rf /usr/share/nginx/html/*
COPY ./ /usr/share/nginx/html


EXPOSE 80

