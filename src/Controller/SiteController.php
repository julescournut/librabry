<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Genre;
use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Livraison;
use App\Entity\DetailLivraison;
use App\Entity\Pays;
use App\Entity\Utilisateur;
use App\Entity\Adresse;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use App\Repository\AuteurRepository;
use App\Repository\AvisRepository;
use App\Repository\LivraisonRepository;
use App\Repository\PaysRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\DetailLivraisonRepository;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ManagerRegistry;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(LivreRepository $livre_repo)
    {
        $livres = $livre_repo->findby(['tendance' => 1], ['prix' => 'desc']);
        foreach ($livres as $livre) {
            $this->computeBookRatings($livre);
        }
        return $this->render('site/index.html.twig', [
            'livres' => $livres,
        ]);
    }


    public function header(LivraisonRepository $livr_repo, UtilisateurRepository $util_repo, ManagerRegistry $manager)
    {
        $utilisateur_courant = $util_repo->find(1);
        $livraison = $livr_repo->findby(['utilisateur' => $utilisateur_courant, 'statut' => "Non Validée"]);
        if (empty($livraison)) {
            $livraison = new Livraison();
            $livraison->setUtilisateur($utilisateur_courant);
            $livraison->setAdresse($utilisateur_courant->getAdresse());
            $livraison->setStatut('Non Validée');
            $manager = $manager->getManager();
            $manager->persist($livraison);
            $manager->flush();
        } else {
            $livraison = $livraison[0];
        }
        return $this->render('site/header.html.twig', [
            'livraison' => $livraison,
        ]);
    }

    /**
     * @Route("/genre/{id}", name="genre")
     */
    public function genre(Genre $genre, GenreRepository $genre_repo, AuteurRepository $auteur_repo)
    {
        $genres = $genre_repo->findAll();
        $auteurs = $auteur_repo->findAll();
        foreach ($genre->getLivres() as $livre) {
            $this->computeBookRatings($livre);
        }
        return $this->render('site/genre.html.twig', [
            'genre' => $genre,
            'genres' => $genres,
            'auteurs' => $auteurs
        ]);
    }

    /**
     * @Route("/auteur/{id}", name="auteur")
     */
    public function auteur(Auteur $auteur, GenreRepository $genre_repo, AuteurRepository $auteur_repo)
    {
        $genres = $genre_repo->findAll();
        $auteurs = $auteur_repo->findAll();
        foreach ($auteur->getLivres() as $livre) {
            $this->computeBookRatings($livre);
        }
        return $this->render('site/auteur.html.twig', [
            'auteur' => $auteur,
            'genres' => $genres,
            'auteurs' => $auteurs
        ]);
    }

    /**
     * @Route("/livres", name="livres")
     */
    public function livres(GenreRepository $genre_repo, LivreRepository $livre_repo, AuteurRepository $auteur_repo)
    {
        $genres = $genre_repo->findAll();
        $livres = $livre_repo->findAll();
        $auteurs = $auteur_repo->findAll();
        foreach ($livres as $livre) {
            $this->computeBookRatings($livre);
        }
        return $this->render('site/livres.html.twig', [
            'livres' => $livres,
            'genres' => $genres,
            'auteurs' => $auteurs
        ]);
    }

    /**
     * @Route("/livre/{id}", name="livre")
     */
    public function livre(Livre $livre, LivreRepository $livre_repo, ManagerRegistry $manager, LivraisonRepository $livr_repo, UtilisateurRepository $util_repo)
    {
        $this->computeBookRatings($livre);
        $saga = null;
        if ($livre->getSaga() !== null) {
            $saga = $livre_repo->findBy([
                'saga' => $livre->getSaga()
            ], [
                'volume' => 'asc'
            ], 5);
            unset($saga[array_search($livre, $saga)]);
            foreach ($saga as $l) {
                $this->computeBookRatings($l);
            }
        }
        $utilisateur_courant = $util_repo->find(1);
        $livraison = $livr_repo->findby(['utilisateur' => $utilisateur_courant, 'statut' => "Non Validée"]);
        if (empty($livraison)) {
            $livraison = new Livraison();
            $livraison->setUtilisateur($utilisateur_courant);
            $livraison->setAdresse($utilisateur_courant->getAdresse());
            $livraison->setStatut('Non Validée');
            $manager = $manager->getManager();
            $manager->persist($livraison);
            $manager->flush();
        } else {
            $livraison = $livraison[0];
        }
        return $this->render('site/livre.html.twig', [
            'livre' => $livre,
            'saga' => $saga,
            'livraison' => $livraison
        ]);
    }

    public function computeBookRatings($livre)
    {
        $nbNote = 0;
        $totalNote = 0;
        foreach ($livre->getAvis() as $a) {
            $totalNote += $a->getNote();
            $nbNote ++;
        }
        if ($nbNote != 0)
        {
            $livre->moyNote = $totalNote / $nbNote;
        } else
        {
            $livre->moyNote = 0;
        }
    }

    // /**
    //  * @Route("/livraison/{id}", name="livraison")
    //  */
    // public function livraison(Livraison $livraison, PaysRepository $pays_repo)
    // {
    //     $pays = $pays_repo->findAll();
    //     return $this->render('site/livraison.html.twig', [
    //         'livraison' => $livraison,
    //         'pays' => $pays
    //     ]);
    // }

    /**
     * @Route("/livraison_edit/{id}", name="livraison_edit", methods={"GET", "POST"})
     */
    public function livraison_edit(Livraison $livraison, ManagerRegistry $manager, Request $request, PaysRepository $pays_repo)
    {
        $pays = $pays_repo->findAll();

        if($request->request->get('rue')) {
            $livraison->setStatut('En cours');
            $livraison->setDateCommande(new \DateTime());

            $rue = $request->request->get('rue');
            $code_postal = $request->request->get('code_postal');
            $ville = $request->request->get('ville');
            $id_pays = $request->request->get('id_pays');
            $pay = $pays_repo->find($id_pays);

            $adresse = new Adresse();
            $adresse->setRue($rue);
            $adresse->setCodePostal($code_postal);
            $adresse->setVille($ville);
            $adresse->setPays($pay);
            $livraison->setAdresse($adresse);

            $manager = $manager->getManager();
            $manager->persist($adresse);

            foreach ($livraison->getDetailLivraisons() as $detail) {
                $livre = $detail->getLivre();
                $livre->setStock($livre->getStock() - $detail->getQuantite());
                $manager->persist($livre);
            }

            $manager->flush();
        }


        return $this->render('site/livraison.html.twig', [
            'livraison' => $livraison,
            'pays' => $pays
        ]);
    }

    /**
     * @Route("/back/detail/remove/{id}", name="detail_remove")
     */
    public function detail_remove(DetailLivraison $detail, ManagerRegistry $manager)
    {
        $manager = $manager->getManager();
        $manager->remove($detail);
        $manager->flush();

        return $this->redirectToRoute('livraison_edit', ['id' => $detail->getLivraison()->getId()]);
    }

    /**
     * @Route("/acheter_livre/{id}{id_livre}", name="acheter_livre")
     */
    public function acheter_livre(Livraison $livraison, $id_livre, LivreRepository $livre_repo,  DetailLivraisonRepository $detail_repo,  ManagerRegistry $manager)
    {
        $livre = $livre_repo->find($id_livre);
        $detail = $detail_repo->findby([
            "livre" => $livre,
            "livraison" => $livraison
        ]);
        if ($detail == null) {
            $detail = new DetailLivraison();
            $detail->setLivraison($livraison);
            $detail->setLivre($livre);
            $detail->setQuantite(1);
        } else {
            $detail = $detail[0];
            if ($detail->getQuantite() < $livre->getStock()) {
                $detail->setQuantite($detail->getQuantite() + 1);
            }
        }

        $manager = $manager->getManager();
        $manager->persist($detail);
        $manager->flush();

        return $this->redirectToRoute('livraison_edit', ['id' => $livraison->getId()]);
    }

    /**
     * @Route("/commandes", name="commandes")
     */
    public function commandes(UtilisateurRepository $util_repo)
    {
        $utilisateur = $util_repo->find(1);
        return $this->render('site/commandes.html.twig', [
            'utilisateur' => $utilisateur
        ]);
    }

    /**
     * @Route("/setQuantite/{id}{quantite}", name="setQuantite")
     */
    public function setQuantite(DetailLivraison $detail, $quantite, ManagerRegistry $manager)
    {
        $detail->setQuantite($quantite);
        $manager = $manager->getManager();
        $manager->flush();
        return $this->redirectToRoute('livraison_edit', ['id' => $detail->getLivraison()->getId()]);
    }
}
