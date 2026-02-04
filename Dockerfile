FROM nginx:stable-alpine
LABEL org.opencontainers.image.source="https://github.com/to236663/inkseek"

# Remove default nginx content (optional) and copy site files
RUN rm -rf /usr/share/nginx/html/*
COPY ./ /usr/share/nginx/html

# Expose HTTP
EXPOSE 80

# Nginx image defaults to running nginx in foreground; no CMD override needed.