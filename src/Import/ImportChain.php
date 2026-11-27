<?php

namespace Wallabag\Import;

class ImportChain
{
    /** @var array<string, ImportInterface> */
    private array $imports = [];

    /**
     * Add an import to the chain.
     *
     * @param string $alias
     */
    public function addImport(ImportInterface $import, $alias): void
    {
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
}
