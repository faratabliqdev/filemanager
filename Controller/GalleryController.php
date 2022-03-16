<?php

namespace Adsign\FileManagerBundle\Controller;

use Adsign\FileManagerBundle\Datatables\GalleryDatatable;
use Adsign\FileManagerBundle\Entity\Gallery;
use Adsign\FileManagerBundle\Form\GalleryType;
use Adsign\FileManagerBundle\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Gallery controller.
 *
 * @Route("gallery")
 */
class GalleryController extends Controller
{

    /**
     * @param Request $request
     *
     * @Route("/auto-complete", name="file_manager_gallery_ajax_auto_complete")
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $searchString = $request->get('q');
        if (strlen($searchString) < 2) {
            return new JsonResponse("Invalid search query", 406);
        }
        $result = $this->getDoctrine()
            ->getRepository(Gallery::class)
            ->likeGallery($searchString);
        if ($result) {
            $encoders = [
                new JsonEncoder()
            ];
            $normalizers = [
                (new ObjectNormalizer())->setIgnoredAttributes(['tag'])
            ];
            $serializer = new Serializer($normalizers, $encoders);
            $data = $serializer->serialize($result, 'json');
            return new JsonResponse($data, 200, [], true);
        }
        return new JsonResponse("No feedback for this gallery yet, Be the first ");
    }


    /**
     * @Route("/", name="file_manager_gallery_index", methods="GET")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $isAjax = $request->isXmlHttpRequest();
        /** @var DatatableInterface $datatable */
        $datatable = $this->get('sg_datatables.factory')->create(GalleryDatatable::class);
        $datatable->buildDatatable();
        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }
        return $this->render('@AdsignFileManager/gallery/index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/new", name="file_manager_gallery_new", methods="GET|POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newGallery(Request $request)
    {
        $gallery = new Gallery();
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($gallery);
            $em->flush();

            return $this->redirectToRoute('gallery_index');
        }

        return $this->render('@AdsignFileManager/gallery/new.html.twig', [
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_manager_gallery_show", methods="GET")
     * @param Gallery $gallery
     * @return Response
     */
    public function show(Gallery $gallery)
    {
        return $this->render('@AdsignFileManager/gallery/show.html.twig', ['gallery' => $gallery]);
    }

    /**
     * @Route("/{id}/edit", name="file_manager_gallery_edit", methods="GET|POST")
     * @param Request $request
     * @param Gallery $gallery
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(Request $request, Gallery $gallery)
    {
        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gallery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render('@AdsignFileManager/gallery/edit.html.twig', [
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_manager_gallery_delete", methods="DELETE")
     * @param Request $request
     * @param Gallery $gallery
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Gallery $gallery)
    {
        if ($this->isCsrfTokenValid('delete'.$gallery->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($gallery);
            $em->flush();
        }

        return $this->redirectToRoute('gallery_index');
    }
}
