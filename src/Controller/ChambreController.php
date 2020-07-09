<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Form\ChammbreAddFormType;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChambreController extends AbstractController
{
    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }
    /**
     * @Route("/chambre/create", name="add_chambre")
     * @IsGranted("ROLE_USER")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(ChammbreAddFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $chambre = $form->getData();
            $numero = sprintf("%03d", $chambre->getNumeroBatiment()).'-'.sprintf("%04d", $chambre->getNumeroBatiment()+rand(0,100));
            $chambre->setNumero($numero);

            $em->persist($chambre);
            $em->flush();

            $this->flashy->success('chambre enregistré avec succès !');
            return $this->redirectToRoute('list_chambre');
        }
       
        return $this->render('chambre/add.html.twig',[
            'form_add_chambre' => $form->createView()
        ]);
    }
    /**
     * @Route("/chambre/list", name="list_chambre")
     * @IsGranted("ROLE_USER")
     */
    public function list(ChambreRepository $repo,Request $request,PaginatorInterface $paginator)
    {
        $chambreQuery = $repo->findAll();

        $chambre = $paginator->paginate(
                $chambreQuery,
                $request->query->getInt('page', 1),
                2
            );
        return $this->render('chambre/list.html.twig',[
            'chambres'=>$chambre
        ]);
    }
    /**
     * @Route("/chambre/{id}/edit", name="chambre_edit")
     * @IsGranted("ROLE_USER")
     */
    public function edit($id, ChambreRepository $repo,Request $request,EntityManagerInterface $em)
    {
        $chambre = $repo->find($id);
        $form_edit = $this->createForm(ChammbreAddFormType::class,$chambre);

        $form_edit->handleRequest($request);
        if ($form_edit->isSubmitted() && $form_edit->isValid()) {
           $chambre = $form_edit->getData();
           $numero = sprintf("%03d", $chambre->getNumeroBatiment()).'-'.sprintf("%04d", $chambre->getNumeroBatiment()+rand(0,100));
           $chambre->setNumero($numero);

           $em->flush();

           $this->addFlash('success', 'chambre modifié avec succès !');
           return $this->redirectToRoute('list_chambre');
        }
        return $this->render('chambre/edit.html.twig',[
            'form_edit'=>$form_edit->createView()
        ]);
    }
    /**
     * @Route("/chambre/delete/{id}", name="chambre_delete",methods={"POST","GET"})
     * @IsGranted("ROLE_USER")
     */
    public function delete($id, ChambreRepository $repo,Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $chambre = $repo->find($id);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($chambre);
            
            $entityManager->flush();

            $this->flashy->error('suppression réussi !');
            return new JsonResponse('deleted');
        }
        
        return new JsonResponse("tu n'es pas malin ! clic sur le bouton ou tu degage way !");
        //return $this->redirectToRoute('list_chambre');
    }
}
