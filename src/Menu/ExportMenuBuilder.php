<?php

namespace Wallabag\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;

class ExportMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Config $settings,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_entry_exports')]
    public function createEntryMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry) {
            throw new \InvalidArgumentException('The entry option must be an Entry.');
        }

        $menu = $this->factory->createItem('entry_exports');

        if ($this->authorization->isGranted('EXPORT', $entry)) {
            $this->addFormats($menu, 'export_entry', ['entry' => $entry->getId()]);
        }

        return $menu;
    }

    #[AsMenuBuilder(name: 'wallabag_list_exports')]
    public function createListMenu(array $options): ItemInterface
    {
        if (!isset($options['category'], $options['filters']) || !\is_string($options['category']) || !\is_array($options['filters'])) {
            throw new \InvalidArgumentException('The category and filters options are required.');
        }

        $category = $options['category'];
        $filters = $options['filters'];
        $parameters = ['category' => $category];

        if ('tag_entries' === $category) {
            $parameters['tag'] = $filters['tag'] ?? null;
        } elseif ('search' === $category) {
            $parameters['search_entry'] = ['term' => $filters['search_entry']['term'] ?? ''];
            $parameters['currentRoute'] = $filters['currentRoute'] ?? '';
        } elseif ('same_domain' === $category) {
            $parameters['entry'] = $filters['entry'] ?? null;
        }

        $menu = $this->factory->createItem('list_exports');

        if ($this->authorization->isGranted('EXPORT_ENTRIES')) {
            $this->addFormats($menu, 'export_entries', $parameters);
        }

        return $menu;
    }

    private function addFormats(ItemInterface $menu, string $route, array $parameters): void
    {
        $listExport = 'export_entries' === $route;

        if ($this->settings->get('export_epub')) {
            $menu->addChild('epub', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'epub'],
                'label' => 'EPUB',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate ePub file'],
            ]);
        }

        if ($this->settings->get('export_pdf')) {
            $menu->addChild('pdf', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'pdf'],
                'label' => 'PDF',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate PDF file'],
            ]);
        }

        if ($this->settings->get('export_csv')) {
            $menu->addChild('csv', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'csv'],
                'label' => 'CSV',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate CSV file'],
            ]);
        }

        if ($this->settings->get('export_json')) {
            $menu->addChild('json', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'json'],
                'label' => 'JSON',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate JSON file'],
            ]);
        }

        if ($this->settings->get('export_txt')) {
            $menu->addChild('txt', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'txt'],
                'label' => 'TXT',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate TXT file'],
            ]);
        }

        if ($this->settings->get('export_xml')) {
            $menu->addChild('xml', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'xml'],
                'label' => 'XML',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate XML file'],
            ]);
        }

        if ($this->settings->get('export_md')) {
            $menu->addChild('md', [
                'route' => $route,
                'routeParameters' => $parameters + ['format' => 'md'],
                'label' => 'Markdown',
                'attributes' => $listExport ? ['class' => 'bold'] : [],
                'linkAttributes' => $listExport ? ['class' => 'waves-effect'] : ['title' => 'Generate MD file'],
            ]);
        }
    }
}
