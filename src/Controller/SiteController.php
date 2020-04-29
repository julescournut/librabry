<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Genre;
use App\Entity\Livre;
use App\Entity\Auteur;
use App\Entity\Livraison;
use App\Entity\Pays;
use App\Repository\GenreRepository;
use App\Repository\LivreRepository;
use App\Repository\AuteurRepository;
use App\Repository\AvisRepository;
use App\Repository\LivraisonRepository;
use App\Repository\PaysRepository;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(LivreRepository $livre_repo)
    {
        $livres = $livre_repo->findby([], ['prix' => 'desc'], 8);
        foreach ($livres as $livre) {
            $this->computeBookRatings($livre);
        }
        return $this->render('site/index.html.twig', [
            'livres' => $livres,
        ]);
    }

    public function header(GenreRepository $genre_repo)
    {
        $genres = $genre_repo->findAll();
        return $this->render('site/header.html.twig', [
            'genres' => $genres,
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
    public function livre(Livre $livre, LivreRepository $livre_repo)
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
        return $this->render('site/livre.html.twig', [
            'livre' => $livre,
            'saga' => $saga,
        ]);
    }

    /**
     * @Route("/livre_new", name="livre_new")
     */
    public function livre_new(AuteurRepository $auteur_repo)
    {
        $auteurs = $auteur_repo->findAll();
        return $this->render('site/livre_new.html.twig', [
            'auteurs' => $auteurs
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

    /**
     * @Route("/livraison/{id}", name="livraison")
     */
    public function livraison(Livraison $livraison, PaysRepository $pays_repo)
    {
        $pays = $pays_repo->findAll();
        return $this->render('site/livraison.html.twig', [
            'livraison' => $livraison,
            'pays' => $pays
        ]);
    }
}
