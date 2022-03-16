<?php

namespace Adsign\FileManagerBundle\Controller;

use Adsign\FileManagerBundle\Entity\Tag;
use Adsign\FileManagerBundle\Form\TagType;
use Adsign\FileManagerBundle\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @Route("/tag")
 */
class TagController extends Controller
{

    /**
     * @param Request $request
     *
     * @Route("/auto-complete", name="file_manager_tag_ajax_auto_complete")
     * @return Response
     */
    public function autoCompleteAction(Request $request)
    {
//        die();
//        $stopwatch = new Stopwatch(true);
//        $stopwatch->start('ajax');
        $searchString = $request->get('q');
        if (strlen($searchString) < 2) {
            return new Response("Invalid search query", 406);
        }

        $result = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->likeTag($searchString);
//        $stopwatch->lap('ajax');
        if ($result) {
            $encoders = [
                new JsonEncoder()
            ];
            $normalizers = [
                (new ObjectNormalizer())->setIgnoredAttributes(['tag'])
            ];
            $serializer = new Serializer($normalizers, $encoders);
            $data = $serializer->serialize($result, 'json');
//            $stopwatch->lap('ajax');
//            $event = $stopwatch->stop('ajax');
//            var_dump($event);
//            die();
            return new JsonResponse($data, 200, [], true);
        }
//        $event = $stopwatch->stop('ajax');
        return new JsonResponse("No feedback for this tag yet, Be the first ");
    }

    /**
     * @Route("/", name="file_manager_tag_index", methods="GET")
     * @param TagRepository $tagRepository
     * @return Response
     */
    public function index(TagRepository $tagRepository)
    {
        return $this->render('@AdsignFileManager/tag/index.html.twig', ['tags' => $tagRepository->findAll()]);
    }

    /**
     * @Route("/new", name="file_manager_tag_new", methods="GET|POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newTag(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            return $this->redirectToRoute('tag_index');
        }

        return $this->render('@AdsignFileManager/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_manager_tag_show", methods="GET")
     * @param Tag $tag
     * @return Response
     */
    public function show(Tag $tag)
    {
        return $this->render('@AdsignFileManager/tag/show.html.twig', ['tag' => $tag]);
    }

    /**
     * @Route("/{id}/edit", name="file_manager_tag_edit", methods="GET|POST")
     * @param Request $request
     * @param Tag $tag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, Tag $tag)
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tag_edit', ['id' => $tag->getId()]);
        }

        return $this->render('@AdsignFileManager/tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_manager_tag_delete", methods="DELETE")
     * @param Request $request
     * @param Tag $tag
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Tag $tag)
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tag);
            $em->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}
