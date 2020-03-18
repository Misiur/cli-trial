FROM composer:1.10

WORKDIR /var/app
ADD . .
ENTRYPOINT [ "php", "git.php" ]