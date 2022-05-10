<?php

namespace Michaelr0\LOIA;

use Exception;

class ImageBuilder
{
    protected string $target; // bcm27xx/bcm2711
    protected string $profile; // rpi-4
    protected string $version; // 21.02.3
    protected string $filesystem; // squashfs, ext4, ext4fs, ubifs or jffs2
    protected array $packages = [];
    protected string $uci_defaults = '';

    /**
     * This parameter determines if requested packages are seen as additional or absolute.
     * If set to true the packages are seen as absolute and all default packages outside the requested packages are removed.
     */
    protected bool $diff_packages = false;

    /**
     * Get an ImageBuilder instance for a particular Openwrt version.
     *
     * @return static
     */
    public static function make(string $target, string $profile, string $version)
    {
        return new static($target, $profile, $version);
    }

    /**
     * Check status of a previously triggered build.
     */
    public static function check_build(string $hash): array
    {
        return ImageBuilderApi::check_build($hash);
    }

    /**
     * Create an ImageBuilder instance for a particular Openwrt target, profile and version.
     *
     * @return void
     */
    public function __construct(string $target, string $profile, string $version)
    {
        $this->target = $target;
        $this->profile = $profile;
        $this->version = $version;
    }

    /**
     * Request a custom firmware image.
     */
    public function build(): array
    {
        $options = [
            'defaults' => $this->uci_defaults,
            'diff_packages' => $this->diff_packages,
        ];

        if (!empty($this->filesystem)) {
            $options['filesystem'] = $this->filesystem;
        }

        return ImageBuilderApi::build($this->target, $this->profile, $this->version, $this->packages, $options);
    }

    /**
     * List of packages, either additional or absolute depending on the diff_packages parameter.
     * Defaults to adding packages as additional packages.
     *
     * replaceDefaultPackages() can be used to enable or disable diff_packages.
     *
     * @return $this
     */
    public function packages(array $packages = []): self
    {
        $this->packages = $packages;

        return $this;
    }

    /**
     * Custom shell script embedded in firmware image to be run on first boot.
     * This feature might be dropped in the future. Size is limited to 10kB and can not be exceeded.
     *
     * @return $this
     */
    public function uciDefaults(string $defaults): self
    {
        $this->uci_defaults = $defaults;

        return $this;
    }

    /**
     * Ability to specify filesystem running on device.
     * Attaching this optional parameter will limit the ImageBuilder to only build firmware with that filesystem.
     *
     * Accepted values are: squashfs, ext4, ubifs or jffs2
     *
     * @return $this
     *
     * @throws Exception
     */
    public function filesystem(string $filesystem): self
    {
        if (!in_array($filesystem, ['squashfs', 'ext4', 'ext4fs', 'ubifs', 'jffs2'])) {
            throw new Exception("Invalid filesystem; specified {$filesystem} is not one of the following (squashfs, ext4, ubifs, jffs2)");
        }

        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * This parameter determines if requested packages are seen as additional or absolute.
     * If set to true the packages are seen as absolute and all default packages outside the requested packages are removed.
     *
     * @return $this
     */
    public function replaceDefaultPackages(bool $enabled = true): self
    {
        $this->diff_packages = $enabled;

        return $this;
    }
}
