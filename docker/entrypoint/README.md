# Entrypoint

_Entrypoint is a common way to allow docker images to be "extended" lightly._


It's executed at start if mounted into docker-entrypoint-initdb.d/ in container.
And hence you avoid having to extend the docker image with your own image _(and hence need to build it and so on)_.

It depends on image what kind of files are supported, typically all support shell scripts.
In case of mysql/mariadb/postgres they also support .sql files to init database.

See `docker-compose.yml` for usage.
