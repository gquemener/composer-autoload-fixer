<?php

declare(strict_types = 1);

namespace PhpFQCNFixer\Infrastructure\PhpNamespaceResolver\Composer;

use PhpFQCNFixer\Model\PhpNamespaceResolver\Composer\NamespaceFinder;
use PhpFQCNFixer\Model\PhpNamespaceResolver\Composer\ClassloaderLoader;
use PhpFQCNFixer\Model\File\PathExpander;

final class Psr0NamespaceFinder implements NamespaceFinder
{
    private $loader;
    private $pathExpander;

    public function __construct(ClassloaderLoader $loader, PathExpander $pathExpander)
    {
        $this->loader = $loader;
        $this->pathExpander = $pathExpander;
    }

    public function find(string $path): array
    {
        $classloader = $this->loader->load($path);
        $locations = [];
        foreach ($classloader->getPrefixes() as $prefix => $directories) {
            foreach ($directories as $directory) {
                $directory = $this->pathExpander->expand($directory);
                if (0 === strpos($path, $directory)) {
                    $locations[] = $directory;
                }
            }
        }

        return array_map(function(string $location) use ($path) {
            return str_replace(DIRECTORY_SEPARATOR, '\\', dirname(ltrim(
                str_replace($location, '', $path),
                DIRECTORY_SEPARATOR
            )));
        }, $locations);
    }
}
