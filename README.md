# Celtic34fr CRM Contact Extension

Author: Gilbert ARMENGAUD

This Bolt extension provide a Contact Form and all the method to manage communication between visitors and the website.

Installation:

for last stable version
```bash
composer require celtic34fr/crm-contact
```
for current development version
```bash
composer req celtic34fr/crm-contact:dev-master
```

## Running PHPStan and Easy Codings Standard

First, make sure dependencies are installed:

    celtic34fr/crm-core

```
COMPOSER_MEMORY_LIMIT=-1 composer update
```

And then run ECS:

```
vendor/bin/ecs check src
```
