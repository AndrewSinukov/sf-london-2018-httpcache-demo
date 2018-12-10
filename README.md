Symfony Live London 2018 - Take your Http caching to the next level with xkey & Fastly
======================================================================================

Talk: https://joind.in/talk/18336

This is a fork of ["Symfony Demo Application"](https://github.com/symfony/demo):
- In `xkey` branch it adds code to show FosHttpCache and Varnish xkey usage, and Docker images to demonstrate it.
- In `fastly` branch it adapts that to show an example _(simple poc)_ of how a Fastly Proxy Client for FosHttpCache can be made.

Requirements
------------

For full docker setup usage:
  * Docker + Docker Compose in some 2018 flavour or higher
  * For combining use of local PHP + Docker, use either PHP 7.1 or 7.2 _(7.2 is default, see `docker-composer.yml` to change to 7.1)_


For plain PHP web server usage _(no caching, but able to see raw headers and browse around the demo)_:
  * PHP 7.1.3 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][1].


Usage
-----


#### Installation

```bash
$ git clone https://github.com/andrerom/sf-london-2018-httpcache-demo.git
$ cd sf-london-2018-httpcache-demo
$ composer install
```

_At this point, or later if you want, adapt generated .env to enable APP_ENV=prod and APP_DEBUG=0
In prod you'll be able to see things being cached, while in dev you'll be able to see all headers._


##### Option A: Docker usage

```bash
$ docker-compose up --abort-on-container-exit --force-recreate

# To debug calls to Varnish in separate console (see bottom of docker-compose.yml for more info):
$ docker-compose exec varnish varnishlog -g request -q "ReqMethod eq 'PURGEKEYS'"
```

On Docker for Mac you'll for instance end up with:
- http://localhost:8081 _(Varnish, to see headers and Varnish in action)_
- http://localhost:8080 _(Nginx, to see headers from backend)_

_Check documentation for your docker installation if in doubt._

##### Option B: PHP webserver usage _(mainly just to see headers)_

_TODO: Enable AppCache in this repo (only when not using Varnish...), to showcase most feature of FosHttpCache still working._

```bash
$ php bin/console server:run
```


Overview of code changes from Symfony Demo (`xkey` branch)
----------------------------------------------------------

_TIP You can also see this online in comparison view:_ https://github.com/symfony/demo/compare/master...andrerom:xkey     

A Docker setup with Varnish _(with xkey VMOD)_,  Nginx, PHP & MariaDB stack, see:
- `docker-compose.yml`
- `docker/*`

Adds FosHttpCache by means of:
- `composer require friendsofsymfony/http-cache-bundle guzzlehttp/psr7 php-http/guzzle6-adapter`
- Configure it in `config/packages/fos.yml`

Adjusts frontend `src/Controller/BlogController.php` to showcase caching + tagging for:
- `index()` action _(en/blog/)_
- `postShow()` action _(en/blog/posts/*)_

For cache invalidation backend `src/Controller/Admin/BlogController.php` is modified on:
- `new()` clear `posts` tag _(frontend index view)_
- `edit()` and `delete()` is modified to invalidate `post-<id>` and `post` tags for the two affected frontend views.

Fixes bug in Symfony Demo using flash messages triggering session start:
- See `templates/default/_flash_messages.html.twig`
  - TIP: See link inline there on how to change flash messages to be cache safe _(not end up storing flash messages in cache..)_.
- Ref: https://symfony.com/doc/current/session/avoid_session_start.html

Enabling User Context Hash feature to explore caching pages for users with sessions and logged in users:
- `config/packages/fos.yml` (`user_context` section)
- `config/packages/security.yaml`
- `config/routes/fos.yml`
- `docker/varnish/default.vcl` _(adds `fos_user_context*.vcl` imports)_
- `src/Controller/BlogController.php` _(adds `vary={"X-User-Context-Hash"}` on Cache annotation)_
- _NOTE: This is broken ATM in the code, so you'll need to remember to delete cookie to receive cached pages right now._


##### CHALLENGE: Adapt demo Blog Post Comment feature to be cache safe!!

Easy pick?
1. This issue you should be able to figure out with the info found in code + slides here, but since comments are
   rendered inline in Blog post `post_show.html.twig` template, `commentNew()` action needs to be adapted to invalidate
   tag for post entities.  

Medium to hard?
_If cache where fully enabled also when having session/logged-in within the demo, we will run into 2 issues:

2. New Comment form when you are logged in: This contains a CSRF token, this can not be cached.
   It will end up with error for other users, and is a security issue as it will expose someone else's token.

3. Let's say we want to add Flash message to `commentNew()`, how can you do that in a cache safe way?

Hard? _(Effort: Rather large, so take this as a taught experiment to suggest upstream how it could be done)_
4. By now the talk + code + the answers you have found above have just made it within reach to see how even the
  admin backend even can be cached with some careful handling.


How would you approach these? Feel free to open issue to discuss, or even a PR so others can have a look.
_And who knows, maybe all of this at a later point ends up in Symfony Demo in some form to serve as best practice ;)_
