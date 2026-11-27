<?php

namespace Wallabag\Import;

class ImportChain
{
    /** @var array<string, ImportInterface> */
    private array $imports = [];

    /** @var array<string, ImportInterface> */
    private array $disabledImports = [];

    /** @var string[] */
    private array $disabledAliases;

    /**
     * @param array<string|null> $disabledImporters Aliases of the importers disabled on this instance
     */
    public function __construct(array $disabledImporters = [])
    {
        $this->disabledAliases = array_values(array_filter(
            array_map(static fn ($alias) => trim((string) $alias), $disabledImporters),
            static fn (string $alias) => '' !== $alias,
        ));
    }

    /**
     * Add an import to the chain.
     *
     * @param string $alias
     */
    public function addImport(ImportInterface $import, $alias): void
    {
        if (\in_array($alias, $this->disabledAliases, true)) {
            $this->disabledImports[$alias] = $import;

            return;
        }

        $this->imports[$alias] = $import;
    }

    /**
     * Get all enabled imports.
     *
     * @return array<ImportInterface>
     */
    public function getAll()
    {
        return $this->imports;
    }

    /**
     * @return string[]
     */
    public function getAllAliases(): array
    {
        return [...array_keys($this->imports), ...array_keys($this->disabledImports)];
    }
}
