docker build -t my-php-app . && \
docker run -p 8000:8000 --rm -d --name my-running-app my-php-app
