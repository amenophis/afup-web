<?php

namespace AppBundle\Controller\Admin\Site;

use AppBundle\Controller\SiteBaseController;
use Afup\Site\Logger\DbLoggerTrait;
use AppBundle\Site\Form\RubriqueType;
use AppBundle\Site\Model\Repository\RubriqueRepository;
use AppBundle\Site\Model\Rubrique;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Exception;


class AddRubriqueAction extends SiteBaseController
{
    use DbLoggerTrait;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var Environment */
    private $twig;

    /** @var RubriqueRepository */
    private $rubriqueRepository;

    /** @var string */
    private $storageDir;

    public function __construct(
        RubriqueRepository $rubriqueRepository,
        Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        FlashBagInterface $flashBag,
        $storageDir
    ) {
        $this->rubriqueRepository =  $rubriqueRepository;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
        $this->flashBag = $flashBag;
        $this->storageDir = $storageDir;
    }

    public function __invoke(Request $request)
    {
        $rubrique = new Rubrique();
        $form = $this->createForm(RubriqueType::class, $rubrique);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*Handling the date : */
            $date = $form->get('icone')->getData();
            if ($date) {
              //  $rubrique->setDate($date)
            }
            /* Handling the icon file : */
            $file = $form->get('icone')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = hash('sha1', $originalFilename);
                $newFilename = $safeFilename . '.' . $file->guessExtension();
                try {
                    $file->move($this->storageDir, $newFilename);
                    $rubrique->setIcone($newFilename);
                } catch (FileException $e) {
                    $this->flashBag->add('error', 'Une erreur est survenue lors du traitement de l\'icône');
                }
            }
            try {
                $this->rubriqueRepository->save($rubrique);
                $this->log('Ajout de la rubrique ' . $rubrique->getNom());
                $this->flashBag->add('notice', 'La rubrique '. $rubrique->getNom() .' a été ajoutée');
                return new RedirectResponse($this->urlGenerator->generate('admin_site_rubriques_list', ['filter' => $rubrique->getNom()]));
            } catch (Exception $e) {
                throw $e;
                $this->flashBag->add('error', 'Une erreur est survenue  lors de l\'ajout de la rubrique');
            }
        }

        return new Response($this->twig->render('admin/site/rubrique_form.html.twig', [
            'form' => $form->createView(),
            'formTitle' => 'Ajouter une rubrique',
            'icone' => false,
        ]));
    }
}
