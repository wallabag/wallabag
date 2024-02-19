<?php

namespace Wallabag\Import;

class ImportChain
{
    private $imports;

    public function __construct()
    {
        $this->imports = [];
    }

    /**
     * Add an import to the chain.
     *
     * @param string $alias
     */
    public function addImport(ImportInterface $import, $alias)
    {
        $this->imports[$alias] = $import;
    }

    /**
     * Get all imports.
     *
     * @return array<ImportInterface>
     */
    public function getAll()
    {
        return $this->imports;
    }
}
