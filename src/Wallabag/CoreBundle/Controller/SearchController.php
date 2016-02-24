<?php

namespace Wallabag\CoreBundle\Controller;

use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\ArticleSearch;
use Wallabag\CoreBundle\Form\Type\EntryFilterType;
use Wallabag\CoreBundle\Form\Type\NewSearchType;
use Pagerfanta\Adapter\ArrayAdapter;
use Elastica\Query\MultiMatch;
use Elastica\Query;

class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/search/{page}", name="search", defaults={"page" = "1"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchFormAction(Request $request, $page = 1)
    {
        $articleSearch = new ArticleSearch();

        $form = $this->createForm(NewSearchType::class, $articleSearch);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($this->getParameter('elastic_search')) {
                $finder = $this->get('fos_elastica.finder.wallabag');

                $search = $articleSearch->getSearchTerm();

                $query = new Query();
                $matching = new MultiMatch();
                $matching->setType(MultiMatch::TYPE_MOST_FIELDS);
                $matching->setQuery($search);
                $matching->setFields(array(
                    'title^3',
                    'content',
                ));
                $query->setQuery($matching);

                $articles = $finder->find($query);
            } else {
                $repository = $this->get('wallabag_core.entry_repository');
                $articles = $repository->getArticlesSearched($this->getUser(), $articleSearch->getSearchTerm());
            }

            $adapter = new ArrayAdapter($articles);
            $entries = new Pagerfanta($adapter);

            $entries->setMaxPerPage($this->getUser()->getConfig()->getItemsPerPage());
            $entries->setCurrentPage($page);
            $form = $this->createForm(EntryFilterType::class);

            return $this->render(
                'WallabagCoreBundle:Entry:entries.html.twig',
                array(
                    'form' => $form->createView(),
                    'entries' => $entries,
                    'currentPage' => $page,
                )
            );
        }

        return $this->render('WallabagCoreBundle:Search:new_form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @Route("/search-form", name="search-form")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        return $this->render('WallabagCoreBundle:Search:new.html.twig');
    }
}
