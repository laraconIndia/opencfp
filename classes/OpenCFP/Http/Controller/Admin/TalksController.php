<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;

class TalksController extends BaseController
{
    use AdminAccessTrait;

    private function indexAction(Request $req, Application $app)
    {
        $admin_user_id = $app['sentry']->getUser()->getId();
        $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $pager_formatted_talks = $mapper->getAllPagerFormatted($admin_user_id);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($pager_formatted_talks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) {
            return '/admin/talks?page=' . $page;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $template = $app['twig']->loadTemplate('admin/talks/index.twig');
        $templateData = array(
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($pager_formatted_talks)
        );

        return $template->render($templateData);
    }

    public function viewAction(Request $req, Application $app)
    {
        // Get info about the talks
        $talk_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk_id = $req->get('id');
        $talk = $talk_mapper->get($talk_id);
        $all_talks = $talk_mapper->all()
            ->where(['user_id' => $talk->user_id])
            ->toArray();

        // Get info about our speaker
        $user_mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $speaker = $user_mapper->get($talk->user_id)->toArray();;

        // Grab all the other talks and filter out the one we have
        $otherTalks = array_filter($all_talks, function ($talk) use ($talk_id) {
            if ((int) $talk['id'] == (int) $talk_id) {
                return false;
            }

            return true;
        });

        // Build and render the template
        $template = $app['twig']->loadTemplate('admin/talks/view.twig');
        $templateData = array(
            'talk' => $talk,
            'speaker' => $speaker,
            'otherTalks' => $otherTalks
        );

        return $template->render($templateData);
    }

    /**
     * Set Favorited Talk [POST]
     * @param Request     $req Request Object
     * @param Application $app Silex Application Object
     */
    private function favoriteAction(Request $req, Application $app)
    {
        $admin_user_id = (int) $app['sentry']->getUser()->getId();
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');

        if ($status == false) {
            // Delete the record that matches
            $favorite = $mapper->first([
                'admin_user_id' => $admin_user_id,
                'talk_id' => (int) $req->get('id')
            ]);

            return $mapper->delete($favorite);
        }

        $previous_favorite = $mapper->where([
            'admin_user_id' => $admin_user_id,
            'talk_id' => (int) $req->get('id')
        ]);

        if ($previous_favorite->count() == 0) {
            $favorite = $mapper->get();
            $favorite->admin_user_id = $admin_user_id;
            $favorite->talk_id = (int) $req->get('id');

            return $mapper->insert($favorite);
        }

        return true;
    }

    /**
     * Set Selected Talk [POST]
     * @param Request     $req Request Object
     * @param Application $app Silex Application Object
     */
    private function selectAction(Request $req, Application $app)
    {
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk = $mapper->get($req->get('id'));
        $talk->selected = $status;
        $mapper->save($talk);

        return true;
    }
}
