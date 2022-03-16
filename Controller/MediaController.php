<?php

namespace Adsign\FileManagerBundle\Controller;

use Adsign\FileManagerBundle\Entity\Media;
use Adsign\FileManagerBundle\Entity\Tag;
use Adsign\FileManagerBundle\Form\MediaEditType;
use Adsign\FileManagerBundle\Form\MediaType;
use Adsign\FileManagerBundle\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/media")
 */
class MediaController extends Controller
{

    /**
     * @param Request $request
     *
     * @Route("/auto-complete", name="file_manager_media_ajax_auto_complete")
     * @return JsonResponse
     */
    public function autoCompleteAction(Request $request)
    {
        $searchString = $request->get('q');
        if (strlen($searchString) < 2) {
            return new JsonResponse("Invalid search query", 406);
        }
        $result = $this->getDoctrine()
            ->getRepository(Media::class)
            ->likeMedia($searchString);
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
        return new JsonResponse("No feedback for this media yet, Be the first ");
    }


    /**
     * @Route("/search", name="file_manager_media_log", methods="GET|POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mediaSearch(Request $request)
    {
        $searchId = $request->get('search');
//        if(strlen($searchId) < 2)
//            return new Response("Invalid search query", 406);
//        var_dump($searchId);
//        die();
        //ToDo check later
//        $searches = '';
        $searches = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->findOneBy(['id' => $searchId]);


        return $this->render('@AdsignFileManager/media/search.html.twig', [
            'searches' => $searches,
        ]);
    }

    /**
     * @Route("/", name="file_manager_media_index", methods="GET")
     * @param MediaRepository $mediaRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(MediaRepository $mediaRepository)
    {
        return $this->render('@AdsignFileManager/media/index.html.twig', ['media' => $mediaRepository->findAll()]);
    }

    /**
     * @Route("/new", name="file_manager_media_new", methods="GET|POST")
     * TODO add upload file and handling the size , type and other factors
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newMedia(Request $request)
    {
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                /* @var $uploadFile \Symfony\Component\HttpFoundation\File\UploadedFile */
//                $uploadFile = $request->files;
                $uploadFile = $form->get("file")->getData();
//                foreach($files as $file){
//                    var_dump($file);
//                    die();
//                    $uploadFile = $file;
//                $uploadPath = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/';
//                $uploadFile = $request->request->get('file');
//                var_dump($uploadFile);
//                die();
                //ToDo get the upload path from config file
//                $uploadPath = '/../../public' . $uploadFile->getPath();
//                var_dump($uploadPath);
//                die();
                $medium->setExtension($uploadFile->guessExtension());
                $explode = explode('/',$uploadFile->getMimeType(),2);
                $medium->setType($explode[0]);
                $medium->setSize($uploadFile->getSize());
//                list($width, $height) = getimagesize('path to image');

//                $medium->setCreatedBy(1);
//                var_dump($medium->getExtension());
//                die();
//                $medium->setCreatedAt();

//                var_dump($medium->getCreatedAt());
//                die();
                // ToDo check it later
//                $fileName = $medium->getName() . '@' . date('Y-m-d-H-i-s') . '.' . $medium->getExtension();

//                var_dump($fileName);
//                die();
//
//                $uploadedFile = $uploadFile->move($uploadPath, $fileName);
//
//
//                unset($uploadFile);
            } catch (\Exception $e) {
                /* if you don't set $file to a default file here
                 * you cannot execute the next instruction.
                 */
            }

//            $filename = $uploadedFile->getBasename();
//            $extentJson = $uploadedFile->getExtension();


            $em = $this->getDoctrine()->getManager();
            $em->persist($medium);
            $em->flush();

            return $this->redirectToRoute('media_index');
        }

        return $this->render('@AdsignFileManager/media/new.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show", name="file_manager_media_show", methods="GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        $mediaId = $request->get('id');
        $medium = $this->getDoctrine()->getRepository('AdsignFileManagerBundle:Media')->findOneBy(['id' => $mediaId]);
        return $this->render('@AdsignFileManager/media/show.html.twig', ['medium' => $medium]);
    }

    /**
     * @Route("/edit", name="file_manager_media_edit", methods="GET|POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request)
    {
        $mediaId = $request->get('id');
        $post = $request->get('post');
        $medium = $this->getDoctrine()->getRepository('AdsignFileManagerBundle:Media')->findOneBy(['id' => $mediaId]);
        $form = $this->createForm(MediaEditType::class, $medium);
        $form->handleRequest($request);

        if ($post) {
            $mediaEditName = $request->get('name');
//            var_dump($mediaEditName);die;
            // Todo fix old and new
            $fs = new Filesystem();
//            $this->container->getParameter('kernel.root_dir');
//            $webPath = realpath($options['upload_dir']);
//            $this->get('kernel')->getProjectDir().'/'.$options['web_dir'].
            $OldfilePath = $this->get('kernel')->getProjectDir().'/'.$options['web_dir']. $medium->getUrl(). $medium->getName() . $medium->getExtension();
            $NewfilePath = $this->get('kernel')->getProjectDir().'/'.$options['web_dir']. $medium->getUrl().$mediaEditName.$medium->getExtension();
            try {
                $fs->rename($OldfilePath, $NewfilePath);
                $medium->setName($mediaEditName);
                $this->getDoctrine()->getManager()->flush();
                return new JsonResponse(array('status' => 'success','message' => $this->get('translator')->trans('Media edited Successfully')), Response::HTTP_OK);
//              return $this->redirectToRoute('media_edit', ['id' => $medium->getId()]);
            } catch (IOException $exception) {
                return new JsonResponse(array('status' => 'error','message' => $this->get('translator')->trans('Media didn\'t edited')), Response::HTTP_OK);
            }
        }

        return $this->render('@AdsignFileManager/media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="file_manager_media_delete", methods="DELETE")
     * @param Request $request
     * @param Media $medium
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, Media $medium)
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($medium);
            $em->flush();
        }

        return $this->redirectToRoute('media_index');
    }
}
