Symfony Live London 2018 - Take your Http caching to the next level with xkey & Fastly
======================================================================================

Talk: https://joind.in/talk/18336

This is a fork of ["Symfony Demo Application"](https://github.com/symfony/demo):
- In `xkey` branch it adds code to show FosHttpCache and Varnish xkey usage, and Docker images to demonstrate it.
- In `fastly` branch it adapts that to show an example _(simple poc)_ of how a Fastly Proxy Client for FosHttpCache can be made.

Requirements
------------

For plain PHP web server usage _(no caching, but able to see raw headers and browse around the demo)_:
  * PHP 7.1.3 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][1].

_TIP: Looking for Docker & Varnish setup? See `xkey` branch instead._

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


##### Option A: PHP webserver usage _(mainly just to see headers)_

_TODO: Enable AppCache in this repo (only when not using Varnish...), to showcase most feature of FosHttpCache still working._

```bash
$ php bin/console server:run
```


Overview of code changes (`fastly` branch)
------------------------------------------

Main change is addition of `src/ProxyClient/Fastly.php` and config for it.

You can see what has changed using diff view:
https://github.com/andrerom/sf-london-2018-httpcache-demo/compare/xkey...fastly
