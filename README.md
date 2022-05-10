# LOIA
Laravel Openwrt Imagebuilder API

## Installation

You can install the package via composer:

```bash
composer require michaelr0/laravel-openwrt-imagebuilder-api
```

## Api documentation/schema
[https://sysupgrade.openwrt.org/ui/](https://sysupgrade.openwrt.org/ui/)

## Usage

### Requesting a build

Let's say that we wanted to build/request an Openwrt image for a Raspberry Pi 4 Model B.

We can do this by using `ImageBuilder::make(string $target, string $profile, string $version)`

In the above example for the Raspberry Pi 4, the target is `bcm27xx/bcm2711` and the profile is `rpi-4`, the version will be up to you to decide, but in this example I'll use `21.02.3`

```php
$image = \Michaelr0\LOIA\ImageBuilder::make('bcm27xx/bcm2711', 'rpi-4', '21.02.3');
```
The next step is to add any additional configuration that you'd like.

Adding additional packages:
```php
$image->packages(array $packages = ['irqbalance', 'zram-swap']);
```

The ASU Api accepts a parameter called `diff_packages` which when set to true, will replace all default packages on a target/profile with the ones that you specify, by default I have this automatically disabled, but you can enable it with the below function if that is your desire.
```php
$image->replaceDefaultPackages(bool $enabled = true);
```

Depending on which endpoint you use, it is possible to specify some commands which should be executed automatically on first boot after installation or upgrading.
```bash
uci set network.lan.ipaddr='192.168.1.1'
exit 0
```

```php
$defaults = file_get_contents('<the file listed above>');
$image->uciDefaults(string $defaults);
```

You can also change which endpoint you want to use, by using the `endpoint` function on the `ImageBuilderApi` class.
```php
/**
 * Get or update the API endpoint.
 * Defaults to the development server.
 *
 * Official server: https://sysupgrade.openwrt.org/api/v1/
 *
 * Development server: https://asu.aparcar.org/api/v1/ (supports uci defaults).
 *
 * @return string
 */

\Michaelr0\LOIA\ImageBuilderApi::endpoint('https://sysupgrade.openwrt.org/api/v1/');

\Michaelr0\LOIA\ImageBuilderApi::endpoint('https://asu.aparcar.org/api/v1/');
```

You may specify which filesystem to build your image for, if you have not specified a filesystem, then the defaults for the profile will be built.

But you may build for a particular filesystem if you'd like to do so.
```php
$image->filesystem(string $filesystem = 'ext4'); // squashfs, ext4, ubifs or jffs2
```

And finally request the build
```php
$request = $image->build();
```
You can then check the contents of the `$request` response, which depending on the outcome may have different data and status codes, check the API documentation/schema at [https://sysupgrade.openwrt.org/ui/](https://sysupgrade.openwrt.org/ui/), normally this will return the queued response or the details about the successful build job, if the job was successful, you may find the download links for the images on the `url` key within each image found in the `images` key, if the build was queued, then a `request_hash` will be turned.

You may check the status of a build by using the `request_hash`
```php
$request = \Michaelr0\LOIA\ImageBuilderApi::check_build(string $request_hash);
```
If the build was successful, then the response will be identical to the successful build response of the `$image->build()` call from juse before, if the build is still pending then you may receive a response like:
```php
[
  "detail" => "queued",
  "enqueued_at" => "2021-08-22T20:41:32.729065+00:00",
  "queue_position" => "2,",
  "request_hash" => "f74fdcb97a09",
  "status" => 202
]
```

## Testing

```bash
composer test
```

## Credits

-   [Michael Rook](https://github.com/michaelr0)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
