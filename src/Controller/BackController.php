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
use App\Entity\Achat;
use App\Entity\Saga;
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
use App\Repository\AchatRepository;

use App\Form\SagaType;
use App\Form\AuteurType;
use App\Form\GenreType;


class BackController extends AbstractController
{
    /**
     * @Route("/back", name="dashboard")
     */
    public function index(LivraisonRepository $livre_repo, AchatRepository $achat_repo, LivraisonRepository $liv_repo)
    {
        $allAchats = $achat_repo->findAll();
        $allVentes = $liv_repo->findAll();

        $achats_total = 0;
        $ventes_total = 0;
        for ($i = 1; $i <= 12; $i++) {
            $achats[$i] = 0;
            $ventes[$i] = 0;
            $benef_total[$i] = 0;

            foreach ($allAchats as $achat) {
                if ($achat->getDate()->format('m') == $i) {
                    $achats[$i] += $achat->getQuantite() * $achat->getPrixAchat();
                    $achats_total += $achat->getQuantite() * $achat->getPrixAchat();
                }
            }
            foreach ($allVentes as $livraison) {
                if ($livraison->getDateCommande() != null && $livraison->getDateCommande()->format('m') == $i) {
                    foreach ($livraison->getDetailLivraisons() as $detail) {
                        $ventes[$i] += $detail->getQuantite() * $detail->getLivre()->getPrix();
                        $ventes_total += $detail->getQuantite() * $detail->getLivre()->getPrix();
                    }
                }
            }
        }
        return $this->render('back/dashboard.html.twig', [
            'achats_total' => $achats_total,
            'ventes_total' => $ventes_total,
            'achats' => $achats,
            'ventes' => $ventes
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

            $this->addFlash('success', 'Livre '.$livre->getTitre().' ajouté !');

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

            $this->addFlash('success', 'Livre '.$livre->getTitre().' modifié !');

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

        $this->addFlash('success', 'Livre '.$livre->getTitre().' supprimé !');

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

            $this->addFlash('success', 'Fournisseur '.$fournisseur->getNom().' ajouté !');

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

            $this->addFlash('success', 'Fournisseur '.$fournisseur->getNom().' modifié !');
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
        $manager->remove($fournisseur->getAdresse());
        $manager->remove($fournisseur);
        $manager->flush();

        $this->addFlash('success', 'Fournisseur '.$fournisseur->getNom().' supprimé !');

        return $this->redirectToRoute('fournisseurs_back');
    }

    /**
     * @Route("/back/achats", name="achats_back")
     */
    public function achats(AchatRepository $achat_repo)
    {
        $achats = $achat_repo->findAll();
        return $this->render('back/achats.html.twig', [
            'achats' => $achats
        ]);
    }

    /**
     * @Route("/back/achat_new", name="achat_new", methods={"GET", "POST"})
     */
    public function achat_new(Request $request, ManagerRegistry $manager, LivreRepository $livre_repo, FournisseurRepository $foun_repo)
    {
        $livres = $livre_repo->findAll();
        $fournisseurs = $foun_repo->findAll();

        if($request->request->get('id_livre')) {
            $id_livre = $request->request->get('id_livre');
            $livre = $livre_repo->find($id_livre);
            $id_fournisseur = $request->request->get('id_fournisseur');
            $fournisseur = $foun_repo->find($id_fournisseur);
            $quantite = $request->request->get('quantite');
            $prix_achat = $request->request->get('prix_achat');

            $achat = new Achat();
            $achat->setLivre($livre);
            $achat->setFournisseur($fournisseur);
            $achat->setQuantite($quantite);
            $livre->setStock($livre->getStock() + $quantite);
            $achat->setPrixAchat($prix_achat);
            $achat->setDate(new \DateTime());

            $manager = $manager->getManager();
            $manager->persist($achat);
            $manager->flush();

            $this->addFlash('success', 'Achat n°'.$achat->getId().' ajouté !');

            return $this->redirectToRoute('achats_back');
        }

        return $this->render('back/achat_new.html.twig', [
            'livres' => $livres,
            'fournisseurs' => $fournisseurs
        ]);
    }

    /**
     * @Route("/back/achat_edit/{id}", name="achat_edit", methods={"GET", "POST"})
     */
    public function achat_edit(Achat $achat, Request $request, ManagerRegistry $manager, LivreRepository $livre_repo, FournisseurRepository $foun_repo)
    {
        $livres = $livre_repo->findAll();
        $fournisseurs = $foun_repo->findAll();

        if($request->request->get('id_livre')) {
            $id_livre = $request->request->get('id_livre');
            $livre = $livre_repo->find($id_livre);
            $id_fournisseur = $request->request->get('id_fournisseur');
            $fournisseur = $foun_repo->find($id_fournisseur);
            $quantite = $request->request->get('quantite');
            $prix_achat = $request->request->get('prix_achat');

            $achat->setLivre($livre);
            $achat->setFournisseur($fournisseur);
            $livre->setStock($livre->getStock() - $achat->getQuantite());
            $livre->setStock($livre->getStock() + $quantite);
            $achat->setQuantite($quantite);
            $achat->setPrixAchat($prix_achat);
            $achat->setDate(new \DateTime());

            $manager = $manager->getManager();
            $manager->flush();

            $this->addFlash('success', 'Achat n°'.$achat->getId().' modifié !');
        }

        return $this->render('back/achat_edit.html.twig', [
            'achat' => $achat,
            'livres' => $livres,
            'fournisseurs' => $fournisseurs
        ]);
    }

    /**
     * @Route("/back/achat/remove/{id}", name="achat_remove")
     */
    public function achat_remove(Achat $achat, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $livre = $achat->getLivre();
        $livre->setStock($livre->getStock() - $achat->getQuantite());
        $manager->remove($achat);
        $manager->flush();

        $this->addFlash('success', 'Achat n°'.$achat->getId().' supprimé !');

        return $this->redirectToRoute('achats_back');
    }

    /**
     * @Route("/back/sagas", name="sagas_back")
     */
    public function sagas(SagaRepository $saga_repo)
    {
        $sagas = $saga_repo->findAll();
        return $this->render('back/sagas.html.twig', [
            'sagas' => $sagas
        ]);
    }

    /**
     * @Route("/back/saga_new", name="saga_new", methods={"GET", "POST"})
     */
    public function saga_new(Request $request, ManagerRegistry $manager)
    {
        $saga = new Saga();
        $form = $this->CreateForm(SagaType::class, $saga);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $manager->getManager();
            $manager->persist($saga);
            $manager->flush();

            $this->addFlash('success', 'Saga '.$saga->getTitre().' ajoutée !');

            return $this->redirectToRoute('sagas_back');
        }

        return $this->render('back/saga_new.html.twig', [
            'sagaform' => $form->createView()
        ]);
    }

    /**
     * @Route("/back/saga_edit/{id}", name="saga_edit", methods={"GET", "POST"})
     */
    public function saga_edit(Saga $saga, Request $request, ManagerRegistry $manager)
    {
        if($request->request->get('titre')) {
            $titre = $request->request->get('titre');

            $saga->setTitre($titre);

            $manager = $manager->getManager();
            $manager->flush();

            $this->addFlash('success', 'Saga '.$saga->getTitre().' modifiée !');
        }

        return $this->render('back/saga_edit.html.twig', [
            'saga' => $saga,
        ]);
    }

    /**
     * @Route("/back/saga/remove/{id}", name="saga_remove")
     */
    public function saga_remove(Saga $saga, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($saga);
        $manager->flush();

        $this->addFlash('success', 'Saga '.$saga->getTitre().' supprimée !');

        return $this->redirectToRoute('sagas_back');
    }

    /**
     * @Route("/back/auteurs", name="auteurs_back")
     */
    public function auteurs(AuteurRepository $auteur_repo)
    {
        $auteurs = $auteur_repo->findAll();
        return $this->render('back/auteurs.html.twig', [
            'auteurs' => $auteurs
        ]);
    }

    /**
     * @Route("/back/auteur_new", name="auteur_new", methods={"GET", "POST"})
     */
    public function auteur_new(Request $request, ManagerRegistry $manager)
    {
        $auteur = new Auteur();
        $form = $this->CreateForm(AuteurType::class, $auteur);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $manager->getManager();
            $manager->persist($auteur);
            $manager->flush();

            $this->addFlash('success', 'Auteur '.$auteur->getNom().' ajouté !');

            return $this->redirectToRoute('auteurs_back');
        }

        return $this->render('back/auteur_new.html.twig', [
            'auteurform' => $form->createView()
        ]);
    }

    /**
     * @Route("/back/auteur_edit/{id}", name="auteur_edit", methods={"GET", "POST"})
     */
    public function auteur_edit(Auteur $auteur, Request $request, ManagerRegistry $manager)
    {
        if($request->request->get('prenom')) {
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');

            $auteur->setPrenom($prenom);
            $auteur->setNom($nom);

            $manager = $manager->getManager();
            $manager->flush();

            $this->addFlash('success', 'Auteur '.$auteur->getNom().' modifié !');
        }

        return $this->render('back/auteur_edit.html.twig', [
            'auteur' => $auteur,
        ]);
    }

    /**
     * @Route("/back/auteur_remove/{id}", name="auteur_remove")
     */
    public function auteur_remove(Auteur $auteur, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($auteur);
        $manager->flush();

        $this->addFlash('success', 'Auteur '.$auteur->getNom().' supprimé !');

        return $this->redirectToRoute('auteurs_back');
    }

    /**
     * @Route("/back/genres", name="genres_back")
     */
    public function genres(GenreRepository $genre_repo)
    {
        $genres = $genre_repo->findAll();
        return $this->render('back/genres.html.twig', [
            'genres' => $genres
        ]);
    }

    /**
     * @Route("/back/genre_new", name="genre_new", methods={"GET", "POST"})
     */
    public function genre_new(Request $request, ManagerRegistry $manager)
    {
        $genre = new Genre();
        $form = $this->CreateForm(GenreType::class, $genre);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager = $manager->getManager();
            $manager->persist($genre);
            $manager->flush();

            $this->addFlash('success', 'Genre '.$genre->getTitre().' ajouté !');

            return $this->redirectToRoute('genres_back');
        }

        return $this->render('back/genre_new.html.twig', [
            'genreform' => $form->createView()
        ]);
    }

    /**
     * @Route("/back/genre_edit/{id}", name="genre_edit", methods={"GET", "POST"})
     */
    public function genre_edit(Genre $genre, Request $request, ManagerRegistry $manager)
    {
        if($request->request->get('titre')) {
            $titre = $request->request->get('titre');

            $genre->setTitre($titre);

            $manager = $manager->getManager();
            $manager->flush();

            $this->addFlash('success', 'Genre '.$genre->getTitre().' modifié !');
        }

        return $this->render('back/genre_edit.html.twig', [
            'genre' => $genre,
        ]);
    }

    /**
     * @Route("/back/genre_remove/{id}", name="genre_remove")
     */
    public function genre_remove(Genre $genre, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($genre);
        $manager->flush();

        $this->addFlash('success', 'Genre '.$genre->getTitre().' supprimé !');

        return $this->redirectToRoute('genres_back');
    }

    /**
     * @Route("/back/livraisons", name="livraisons_back")
     */
    public function livraisons(LivraisonRepository $livr_repo)
    {
        $livraisons = $livr_repo->findAll();
        return $this->render('back/livraisons.html.twig', [
            'livraisons' => $livraisons
        ]);
    }

    /**
     * @Route("/back/delivered/{id}", name="delivered")
     */
    public function delivered(Livraison $livraison, ManagerRegistry $manager)
    {
        $livraison->setStatut('Livrée');
        $livraison->setDateLivraison(new \DateTime());

        $manager = $manager->getManager();
        $manager->flush();

        $this->addFlash('success', 'Commande '.$livraison->getId().' livrée !');

        return $this->redirectToRoute('livraisons_back');
    }
}
