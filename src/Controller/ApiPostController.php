<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post_index", methods={"GET"})
     * @param PostRepository $postRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(PostRepository $postRepository, SerializerInterface $serializer)
    {
        return $this->json($postRepository->findAll(), 200, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/api/post", name="api_post_store", methods={"POST"})
     */
    public function store(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
                          ValidatorInterface $validator) {

        $jsonRecu = $request->getContent();

        try {
            $post = $serializer->deserialize($jsonRecu, Post::class, 'json');

            $post->setCreatedAt(new \DateTime());

            $errors = $validator->validate($post);

            if(count($errors) > 0) {
                return $this->json($errors, 400);
            }

            $em->persist($post);
            $em->flush();

            return $this->json($post, 201, [], ['groups' => 'post:read']);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/api/post/{id}", name="api_post_edit", methods={"PATCH"})
     */
    public function edit(Request $request, Post $post, SerializerInterface $serializer, EntityManagerInterface $em) {

        $jsonRecu = $request->getContent();

        $newPost = $serializer->deserialize($jsonRecu, Post::class, 'json');

        if(!is_null($newPost->getTitle()) || !empty($newPost->getTitle())) {
            $post->setTitle($newPost->getTitle());
        }

        $em->persist($post);
        $em->flush();

        return $this->json($post, 201, [], ['groups' => 'post:read']);
    }
}
