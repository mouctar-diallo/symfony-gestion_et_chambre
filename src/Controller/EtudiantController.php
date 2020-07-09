<?php

namespace App\Controller;

use App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class EtudiantController extends AbstractController
{
    /**
     * @Route("/etudiant/create", name="add_etudiant")
     * @IsGranted("ROLE_USER")
     */
    public function create()
    {
        return $this->render('etudiant/add.html.twig');
    }
    /**
     * @Route("/etudiant/list", name="list_etudiant")
     * @IsGranted("ROLE_USER")
     */
    public function list(EntityManagerInterface $em,EtudiantRepository $repository,Request $request)
    {
        $etudiants = $repository->findAll();
        if ($request->isXmlHttpRequest()) {
            $data = array();
            $index = 0;
            foreach ($etudiants as $etudiant) {
                $temp = array(
                    'id'=>$etudiant->getId(),
                    'matricule'=>$etudiant->getMatricule(),
                    'prenom'=>$etudiant->getPrenom(),
                    'email'=>$etudiant->getEmail(),
                    'type'=>$etudiant->getType(),
                    'adresse'=>$etudiant->getAdresse(),
                    'bourse'=>$etudiant->getBourse(),
                    'numero_chambre'=>$etudiant->getNumeroChambre(),
                );
                $data[$index++] = $temp;
            }

            return new JsonResponse($data);
        }else{
            return $this->render('etudiant/list.html.twig',['etudiants'=>$etudiants]);
        }
    }
    /**
     * @Route("/etudiant/edit", name="edit_etudiant")
     * @IsGranted("ROLE_USER")
     */
    public function edit(EntityManagerInterface $em,EtudiantRepository $repository,Request $request,FlashyNotifier $flashy)
    {
         if ($request->isXmlHttpRequest()) {
             $id = $request->request->get('id');
             $cible = "Set".ucfirst( $request->request->get('cible'));
             $valeur = $request->request->get('valeur');

            $etudiant =  $repository->find($id);
            $etudiant->$cible($valeur);
            $flashy->success('modification réussi');
            $em->flush();

            return new JsonResponse('updated');
         }
         return new JsonResponse("tu n es pas malin ! clic sur le bouton ou tu degage way !");

    }
    /**
     * @Route("/etudiant/delete", name="delete_etudiant")
     * @IsGranted("ROLE_USER")
     */
    public function delete(EtudiantRepository $repository,Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            
            $id = $request->request->get('id');
            $etudiant = $repository->find($id);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($etudiant);
            
            $entityManager->flush();
    
            return new JsonResponse('deleted');
        }

        return new JsonResponse("tu n es pas malin ! clic sur le bouton ou tu degage way !");
    }
}
