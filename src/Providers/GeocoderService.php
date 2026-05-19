<?php

/**
 * This file is part of the Geocoder Laravel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Mike Bronner <mike@genealabs.com>
 * @license MIT License
 */

declare(strict_types=1);

namespace Geocoder\Laravel\Providers;

use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Model\Address;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use PhpToken;
use ReflectionClass;

class GeocoderService extends ServiceProvider
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $defer = false;
    protected static array $discoveredSerializableClasses = [];

    public function boot(): void
    {
        $configPath = __DIR__ . "/../../config/geocoder.php";
        $this->publishes([$configPath => $this->configPath("geocoder.php")], "config");
        $this->mergeConfigFrom($configPath, "geocoder");
        $this->registerSerializableClasses();
    }

    public function provides(): array
    {
        return ["geocoder", ProviderAndDumperAggregator::class];
    }

    public function register(): void
    {
        $this->app->alias("Geocoder", Geocoder::class);
        $this->app->singleton(ProviderAndDumperAggregator::class, function () {
            return (new ProviderAndDumperAggregator)
                ->registerProvidersFromConfig(collect(config("geocoder.providers")));
        });
        $this->app->bind("geocoder", ProviderAndDumperAggregator::class);
    }

    protected function registerSerializableClasses(): void
    {
        if (! config("geocoder.cache.auto_register_serializable_classes", true)) {
            return;
        }

        if (self::$discoveredSerializableClasses === []) {
            self::$discoveredSerializableClasses = $this->discoverSerializableClasses();
        }

        $existing = config("cache.serializable_classes");
        $existing = is_array($existing)
            ? $existing
            : [];

        config([
            "cache.serializable_classes" => collect($existing)
                ->concat(self::$discoveredSerializableClasses)
                ->unique()
                ->values()
                ->toArray(),
        ]);
    }

    protected function discoverSerializableClasses(): array
    {
        $vendorRoot = $this->vendorRoot();

        if ($vendorRoot === null) {
            return [Collection::class];
        }

        return collect([
                "{$vendorRoot}/willdurand/geocoder/Model",
                "{$vendorRoot}/geocoder-php/*/Model",
            ])
            ->flatMap(function (string $pattern): array {
                return glob($pattern)
                    ?: [];
            })
            ->flatMap(function (string $directory): array {
                return glob("{$directory}/*.php")
                    ?: [];
            })
            ->flatMap(function (string $file): array {
                return $this->classNamesFromVendorFile($file);
            })
            ->prepend(Collection::class)
            ->unique()
            ->values()
            ->toArray();
    }

    protected function vendorRoot(): ?string
    {
        $addressFile = (new ReflectionClass(Address::class))->getFileName();

        if ($addressFile === false) {
            return null;
        }

        $directory = dirname($addressFile);

        while (
            $directory !== ""
            && $directory !== "/"
            && basename($directory) !== "vendor"
        ) {
            $parent = dirname($directory);

            if ($parent === $directory) {
                return null;
            }

            $directory = $parent;
        }

        return basename($directory) === "vendor"
            ? $directory
            : null;
    }

    protected function classNamesFromVendorFile(string $file): array
    {
        $contents = file_get_contents($file);

        if ($contents === false) {
            return [];
        }

        return $this->extractClassesFromTokens($this->tokenize($contents));
    }

    protected function tokenize(string $contents): array
    {
        return array_values(array_filter(
            PhpToken::tokenize($contents),
            fn (PhpToken $token): bool => ! $token->is([T_WHITESPACE, T_COMMENT, T_DOC_COMMENT]),
        ));
    }

    protected function extractClassesFromTokens(array $tokens): array
    {
        $namespace = "";
        $classes = [];

        foreach ($tokens as $tokenIndex => $token) {
            if ($token->is(T_NAMESPACE)) {
                $namespace = $this->readNamespaceAt($tokens, $tokenIndex + 1);

                continue;
            }

            if (! $this->isClassDeclaration($tokens, $tokenIndex)) {
                continue;
            }

            $classes[] = $this->qualify($namespace, $tokens[$tokenIndex + 1]->text);
        }

        return $classes;
    }

    protected function isClassDeclaration(array $tokens, int $tokenIndex): bool
    {
        if (
            ! $tokens[$tokenIndex]->is(T_CLASS)
            || (
                $tokenIndex > 0
                && $tokens[$tokenIndex - 1]->is(T_NEW)
            )
        ) {
            return false;
        }

        return isset($tokens[$tokenIndex + 1])
            && $tokens[$tokenIndex + 1]->is(T_STRING);
    }

    protected function qualify(string $namespace, string $name): string
    {
        return $namespace !== ""
            ? "{$namespace}\\{$name}"
            : $name;
    }

    protected function readNamespaceAt(array $tokens, int $startingTokenIndex): string
    {
        $namespaceParts = [];
        $tokenCount = count($tokens);

        for ($tokenIndex = $startingTokenIndex; $tokenIndex < $tokenCount; $tokenIndex++) {
            if (! $tokens[$tokenIndex]->is([T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR])) {
                break;
            }

            $namespaceParts[] = $tokens[$tokenIndex]->text;
        }

        return implode("", $namespaceParts);
    }

    protected function configPath(string $path = ""): string
    {
        if (function_exists("config_path")) {
            return config_path($path);
        }

        $pathParts = [
            app()->basePath(),
            "config",
            trim($path, "/"),
        ];

        return implode("/", $pathParts);
    }
}
