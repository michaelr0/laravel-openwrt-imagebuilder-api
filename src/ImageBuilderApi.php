<?php

namespace Michaelr0\LOIA;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Laravel wrapper for Openwrt ASU Image Builder API.
 *
 * API Schema: https://sysupgrade.openwrt.org/ui/.
 */
class ImageBuilderApi
{
    /**
     * API Endpoint.
     *
     * Official server: sysupgrade.openwrt.org
     *
     * Development server: asu.aparcar.org (supports uci defaults).
     *
     * @var string
     */
    // protected static $endpoint = 'https://sysupgrade.openwrt.org/api/v1/';
    protected static $endpoint = 'https://asu.aparcar.org/api/v1/';

    /**
     * Request a custom firmware image.
     *
     * This API call allows to request a firmware image containing any selection of packages.
     * If the build was successfull it will respond a 200 response including information on the build and created images.
     * If there were errors other status coulds will be returned, as described below.
     *
     * Since images take between 30 seconds and 5 minutes to be build,
     * the status 202 response will be returend while an image is being build or in the build queue.
     * Clients should poll the API every 5 seconds to if the image was build or an error occured.
     *
     * The POST request should only be done once.
     * A valid request will result in a response including a request_hash which can
     * then be used for the check_build($request_hash) method.
     *
     * This way the server doens't have to validate the request every time.
     */
    public static function build(string $target, string $profile, string $version, array $packages = [], array $options = []): array
    {
        $data = array_merge($options, [
            'target' => $target,
            'profile' => $profile,
            'version' => $version,
            'packages' => $packages,
        ]);

        $response = static::post('build', $data)->json();

        if (200 === $response['status']) {
            return static::successful_build($response);
        }

        return $response;
    }

    /**
     * Check status of a previously triggered build.
     */
    public static function check_build(string $request_hash): array
    {
        $response = static::get("build/{$request_hash}")->json();

        if (200 === $response['status']) {
            return static::successful_build($response);
        }

        return $response;
    }

    /**
     * Generates download links for successful builds and appends them as 'url' to the images array.
     */
    protected static function successful_build(array $response): array
    {
        $response['images'] = collect($response['images'])->map(function ($image) use ($response) {
            $image['url'] = Str::replace('api/v1/', 'store/', static::$endpoint)."{$response['bin_dir']}/{$image['name']}";

            return $image;
        })->toArray();

        return $response;
    }

    /**
     * Overview of branches and versions available.
     *
     * This can be used by user interfaces and update clients to check for latest releases.
     * <endpoint>/json/v1/latest.json is used by default, however you can poll the live api, by setting $static to false.
     */
    public static function overview(bool $static = true): array
    {
        if (true === $static) {
            return Http::get(Str::replace('api/v1/', 'json/v1/latest.json', static::$endpoint))->json();
        }

        return static::get('overview')->json();
    }

    /**
     * Receive revision of current target.
     */
    public static function revision(string $version, string $target, string $subtarget): array
    {
        return static::get("revision/{$version}/{$target}/{$subtarget}")->json();
    }

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
    public static function endpoint(string $endpoint = null)
    {
        if (!empty($endpoint)) {
            static::$endpoint = $endpoint;
        }

        return static::$endpoint;
    }

    /**
     * Send GET Request.
     */
    public static function get(string $route, array $data = []): Response
    {
        return Http::get(static::$endpoint.$route, $data);
    }

    /**
     * Send POST Request.
     */
    public static function post(string $route, array $data = []): Response
    {
        return Http::post(static::$endpoint.$route, $data);
    }
}
