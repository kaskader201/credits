# Credits

## Create User:
* POST
* https://localhost/v1/user
* ```json
    {"userExternalId": "5c4132cd-4f65-4b22-9c94-bc3d8113aba6"}
  ```
## Add Credits:
* POST
* https://localhost/v1/credit
* ```json
    {
        "amount": 10,
        "userExternalId": "5c4132cd-4f65-4b22-9c94-bc3d8113aba6",
        "creditPriority" : 1,
        "type" : "refund",
        "expiredAt": null,
        "note":"normal"
    }
  ```
* ```json
    {
        "amount": 15,
        "userExternalId": "5c4132cd-4f65-4b22-9c94-bc3d8113aba6",
        "creditPriority" : 2,
        "type" : "marketing",
        "expiredAt": "2024-02-08 13:25:00",
        "note":"expired"
    }
  ```
## Spend Credits:
* POST
* https://localhost/v1/credit/spend
* ```json
    {
        "userExternalId": "7c44dcd0-f17b-479c-a896-bb64e8df7f9f",
        "amount":50
    }
  ```
## Get Balance:
* POST
* https://localhost/v1/balance?userExternalId=5c4132cd-4f65-4b22-9c94-bc3d8113aba6


------------------------





# Symfony Docke


A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework,
with [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/) inside!

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

* Production, development and CI ready
* Just 1 service by default
* Blazing-fast performance thanks to [the worker mode of FrankenPHP](https://github.com/dunglas/frankenphp/blob/main/docs/worker.md) (automatically enabled in prod mode)
* [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
* Automatic HTTPS (in dev and prod)
* HTTP/3 and [Early Hints](https://symfony.com/blog/new-in-symfony-6-3-early-hints) support
* Real-time messaging thanks to a built-in [Mercure hub](https://symfony.com/doc/current/mercure.html)
* [Vulcain](https://vulcain.rocks) support
* Native [XDebug](docs/xdebug.md) integration
* Super-readable configuration

**Enjoy!**

## Docs

1. [Build options](docs/build.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using a Makefile](docs/makefile.md)
8. [Troubleshooting](docs/troubleshooting.md)
9. [Updating the template](docs/updating.md)

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
