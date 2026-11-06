# Installation guide

## System requirements

- [`PHP`](https://www.php.net/downloads) 8.3 or higher with `ext-curl`.
- [`Composer`](https://getcomposer.org/download/) for dependency management.
- [`Yii2`](https://github.com/yiisoft/yii2) 2.0.54 or higher, or the Yii 22 development branch.

## Installation

Install the extension with [Composer](https://getcomposer.org/download/).

```bash
composer require proweb/yii2-ai
```

When using the Yii 22 development branch, the application must explicitly allow it:

```bash
composer require proweb/yii2-ai "yiisoft/yii2:^22.0@dev"
```

The package pulls in [ai-access](https://github.com/dg/ai-access) automatically; there is nothing else to install.

[Back to documentation](../README.md#documentation)
