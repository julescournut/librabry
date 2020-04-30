<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;

use App\Entity\Genre;
use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Livraison;
use App\Entity\Pays;
use App\Entity\Utilisateur;
use App\Entity\Fournisseur;
use App\Entity\Adresse;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use App\Repository\AuteurRepository;
use App\Repository\AvisRepository;
use App\Repository\LivraisonRepository;
use App\Repository\PaysRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\SagaRepository;
use App\Repository\AdresseRepository;
use App\Repository\FournisseurRepository;


class BackController extends AbstractController
{
    /**
     * @Route("/back", name="back")
     */
    public function index()
    {
        return $this->render('back/index.html.twig', [
        ]);
    }

    /**
     * @Route("/back/livres", name="livres_back")
     */
    public function livres(LivreRepository $livre_repo)
    {
        $livres = $livre_repo->findAll();
        return $this->render('back/livres.html.twig', [
            'livres' => $livres
        ]);
    }

    /**
     * @Route("/back/livre_new", name="livre_new", methods={"GET", "POST"})
     */
    public function livre_new(Request $request, ManagerRegistry $manager, AuteurRepository $auteur_repo, GenreRepository $genre_repo, SagaRepository $saga_repo)
    {
        $auteurs = $auteur_repo->findAll();
        $genres = $genre_repo->findAll();
        $sagas = $saga_repo->findAll();

        if($request->request->get('titre')) {
            $titre = $request->request->get('titre');
            $prix = $request->request->get('prix');
            $image = $request->request->get('image');
            $auteur_id = $request->request->get('auteur');
            $auteur = $auteur_repo->find($auteur_id);
            $saga_id = $request->request->get('saga');
            $saga = $saga_repo->find($saga_id);
            $volume = $request->request->get('volume');
            $genres_ids = $request->request->get('genres_ids');
            $resume = $request->request->get('resume');
            $tendance = $request->request->get('tendance');

            $livre = new Livre();
            $livre->setTitre($titre);
            $livre->setPrix($prix);
            $livre->setImage($image);
            $livre->setAuteur($auteur);
            $livre->setSaga($saga);
            if ($volume) {
                $livre->setVolume((int)$volume);
            }
            $livre->setResume($resume);
            $livre->setTendance($tendance);

            foreach ($genres_ids as $genre_id) {
                $genre = $genre_repo->find($genre_id);
                $livre->addGenre($genre);
            }

            $livre->setStock(0);

            $manager = $manager->getManager();
            $manager->persist($livre);
            $manager->flush();

            return $this->redirectToRoute('livres_back');
        }

        return $this->render('back/livre_new.html.twig', [
            'auteurs' => $auteurs,
            'genres' => $genres,
            'sagas' => $sagas
        ]);
    }

    /**
     * @Route("/back/livre_edit/{id}", name="livre_edit", methods={"GET", "POST"})
     */
    public function livre_edit(Livre $livre, Request $request, ManagerRegistry $manager, AuteurRepository $auteur_repo, GenreRepository $genre_repo, SagaRepository $saga_repo)
    {
        $auteurs = $auteur_repo->findAll();
        $genres = $genre_repo->findAll();
        $sagas = $saga_repo->findAll();

        if($request->request->get('titre')) {
            $titre = $request->request->get('titre');
            $prix = $request->request->get('prix');
            $image = $request->request->get('image');
            $auteur_id = $request->request->get('auteur');
            $auteur = $auteur_repo->find($auteur_id);
            $saga_id = $request->request->get('saga');
            $saga = $saga_repo->find($saga_id);
            $volume = $request->request->get('volume');
            $genres_ids = $request->request->get('genres_ids');
            $resume = $request->request->get('resume');
            $tendance = $request->request->get('tendance');

            $livre->setTitre($titre);
            $livre->setPrix($prix);
            $livre->setImage($image);
            $livre->setAuteur($auteur);
            $livre->setSaga($saga);
            if ($volume) {
                $livre->setVolume((int)$volume);
            } else {
                $livre->setVolume(null);
            }
            $livre->setResume($resume);
            $livre->setTendance($tendance);

            foreach ($livre->getGenres() as $genre) {
                $livre->removeGenre($genre);
            }

            foreach ($genres_ids as $genre_id) {
                $genre = $genre_repo->find($genre_id);
                $livre->addGenre($genre);
            }

            $livre->setStock(0);

            $manager = $manager->getManager();
            $manager->flush();
        }

        return $this->render('back/livre_edit.html.twig', [
            'auteurs' => $auteurs,
            'genres' => $genres,
            'sagas' => $sagas,
            'livre' => $livre
        ]);
    }

    /**
     * @Route("/back/livre/remove/{id}", name="livre_remove")
     */
    public function livre_remove(Livre $livre, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($livre);
        $manager->flush();

        $this->addFlash('success', 'Livre supprimé !');

        return $this->redirectToRoute('livres_back');
    }

    /**
     * @Route("/back/fournisseurs", name="fournisseurs_back")
     */
    public function fournisseurs(FournisseurRepository $fourn_repo)
    {
        $fournisseurs = $fourn_repo->findAll();
        return $this->render('back/fournisseurs.html.twig', [
            'fournisseurs' => $fournisseurs
        ]);
    }

    /**
     * @Route("/back/fournisseur_new", name="fournisseur_new", methods={"GET", "POST"})
     */
    public function fournisseur_new(Request $request, ManagerRegistry $manager, PaysRepository $pays_repo)
    {
        $pays = $pays_repo->findAll();

        if($request->request->get('nom')) {
            $nom = $request->request->get('nom');
            $tel = $request->request->get('tel');
            $rue = $request->request->get('rue');
            $code_postal = $request->request->get('code_postal');
            $ville = $request->request->get('ville');
            $id_pays = $request->request->get('id_pays');
            $pays = $pays_repo->find($id_pays);

            $fournisseur = new Fournisseur();
            $fournisseur->setNom($nom);
            $fournisseur->setTel($tel);
            $adresse = new Adresse();
            $adresse->setRue($rue);
            $adresse->setCodePostal($code_postal);
            $adresse->setVille($ville);
            $adresse->setPays($pays);
            $fournisseur->setAdresse($adresse);

            $manager = $manager->getManager();
            $manager->persist($adresse);
            $manager->persist($fournisseur);
            $manager->flush();

            return $this->redirectToRoute('fournisseurs_back');
        }

        return $this->render('back/fournisseur_new.html.twig', [
            'pays' => $pays
        ]);
    }

    /**
     * @Route("/back/fournisseur_edit/{id}", name="fournisseur_edit", methods={"GET", "POST"})
     */
    public function fournisseur_edit(Fournisseur $fournisseur, Request $request, ManagerRegistry $manager, PaysRepository $pays_repo)
    {
        $payss = $pays_repo->findAll();

        if($request->request->get('nom')) {
            $nom = $request->request->get('nom');
            $tel = $request->request->get('tel');
            $rue = $request->request->get('rue');
            $code_postal = $request->request->get('code_postal');
            $ville = $request->request->get('ville');
            $id_pays = $request->request->get('id_pays');
            $pays = $pays_repo->find($id_pays);

            $fournisseur->setNom($nom);
            $fournisseur->setTel($tel);
            $four_adresse = $fournisseur->getAdresse();
            $manager = $manager->getManager();
            if ($four_adresse->getRue() != $rue || $four_adresse->getCodePostal() != $code_postal || $four_adresse->getVille() != $ville || $four_adresse->getPays()->getID() != $id_pays) {
                $adresse = new Adresse();
                $adresse->setRue($rue);
                $adresse->setCodePostal($code_postal);
                $adresse->setVille($ville);
                $adresse->setPays($pays);
                $fournisseur->setAdresse($adresse);
                $manager->persist($adresse);
            }

            $manager->flush();
        }

        return $this->render('back/fournisseur_edit.html.twig', [
            'fournisseur' => $fournisseur,
            'pays' => $payss,
        ]);
    }

    /**
     * @Route("/back/fournisseur/remove/{id}", name="fournisseur_remove")
     */
    public function fournisseur_remove(Fournisseur $fournisseur, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($fournisseur);
        $manager->flush();

        return $this->redirectToRoute('fournisseurs_back');
    }
}
