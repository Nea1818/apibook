<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiGenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="api_genres", methods={"GET"})
     */
    public function list(GenreRepository $repo, SerializerInterface $serializer)
    {
        $genres = $repo->findAll();
        $resultat = $serializer->serialize(
            $genres,
            'json',
            [
                'groups' => ['listGenreFull']
            ]
        );
        
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
    * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
    */
    public function show(Genre $genre, SerializerInterface $serializer)
    {
        $resultat = $serializer->serialize(
            $genre,
            'json',
            [
                'groups' => ['listGenreSimple']
            ]
        );
        
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
    * @Route("/api/genres", name="api_genres_create", methods={"POST"})
    */
    public function create(Request $request, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        // Je récupère les éléments de ma request
        $data = $request->getContent();
        // $genre = new Genre();
        // $serializer->deserialize($data, Genre::class, 'json', ['object_to_populate' => $genre]);
        
        // Je déserialise les datas en objet de type Genre
        $genre = $serializer->deserialize($data, Genre::class, 'json');

        // Gestion des erreurs et validation
        $errors = $validator->validate($genre);
        // S'il y en a je les serialise et les renvoie
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        // Je persist et flush en bdd
        $manager->persist($genre);
        $manager->flush();
        
        // Je ne renvoie rien dans le body à part le code statut et un lien pour rejoindre le nouveau genre
        return new JsonResponse(
            "Le genre a bien été crée",
            Response::HTTP_CREATED,
            ["location" => $this->generateUrl(
                'api_genres_show',
                ["id" => $genre->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )],
            true
        );
    }

    /**
    * @Route("/api/genres/{id}", name="api_genres_update", methods={"PUT"})
    */
    public function edit(Genre $genre, Request $request, ObjectManager $manager, ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $data = $request->getContent();
        $serializer->deserialize(
            $data,
            Genre::class,
            'json',
            ['object_to_populate' => $genre]
        );

        // Gestion des erreurs et validation
        $errors = $validator->validate($genre);
        // S'il y en a je les serialise et les renvoie
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        // Je persist et flush en bdd
        $manager->persist($genre);
        $manager->flush();

        return new JsonResponse("Le genre a bien été modifié", Response::HTTP_OK, [], true);
    }

    /**
    * @Route("/api/genres/{id}", name="api_genres_delete", methods={"DELETE"})
    */
    public function delete(Genre $genre, ObjectManager $manager)
    {
        // Je persist et flush en bdd
        $manager->remove($genre);
        $manager->flush();

        return new JsonResponse("Le genre a bien été supprimé", Response::HTTP_OK, []);
    }
}
