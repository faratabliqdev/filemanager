<?php

namespace Adsign\FileManagerBundle\Controller;

use Adsign\FileManagerBundle\DependencyInjection\ResizeImage;
use Adsign\FileManagerBundle\Entity\Gallery;
use Adsign\FileManagerBundle\Entity\Tag;
use Adsign\FileManagerBundle\Event\FileManagerEvents;
use Adsign\FileManagerBundle\Form\MediaGallerySearchType;
use Adsign\FileManagerBundle\Form\MediaRemoteType;
use Adsign\FileManagerBundle\Helpers\File;
use Adsign\FileManagerBundle\Helpers\FileManager;
use Adsign\FileManagerBundle\Helpers\UploadHandler;
use Adsign\FileManagerBundle\Twig\OrderExtension;
use Adsign\FileManagerBundle\Entity\Media;
use Adsign\FileManagerBundle\Form\MediaSearchType;
use Adsign\FileManagerBundle\Form\MediaType;
use Adsign\FileManagerBundle\Repository\MediaRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Ali Kazemi <Developer@Azrael.ir> , <Kazemi@Adsign.co>
 */
class ManagerController extends Controller
{
    protected $entityManager;
    protected $translator;
    protected $mediaRepository;
    protected $tagRepository;
    protected $galleryRepository;

    // Set up all necessary variable
    protected function initialise()
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $this->mediaRepository = $this->getDoctrine()->getRepository('AdsignFileManagerBundle:Media');
        $this->tagRepository = $this->getDoctrine()->getRepository('AdsignFileManagerBundle:Tag');
        $this->galleryRepository = $this->getDoctrine()->getRepository('AdsignFileManagerBundle:Gallery');
        $this->translator = $this->get('translator');
    }
    /**
     * @var FileManager
     */
    protected $fileManager;

    /**
     * @Route("/", name="file_manager")
     *
     * @param Request $request
     * @return Response
     *
     * @throws \Exception
     */
    public function indexAction(Request $request)
    {
        $this->initialise();
        if(!empty($request->get('limit'))) { $limit = $request->get('limit'); } else {$limit = 30;}
        if(!empty($request->get('page'))) { $page = $request->get('page'); } else {$page =1;}
        $offset = $limit *($page-1);
//        $mediaRepository = $this->mediaRepository;
        $searchId = $request->get('media_search');
        $refresh = $request->get('refresh');
//        $searchGalleryId = $request->get('media_gallery_search');
        $media = null;
        $isSearch = false;
//        var_dump($request->request->all());
//        die();
        if($searchId && !$refresh) {
//            return new Response("Invalid search query - empty Query", 406);
//            if ($searchId['istag'] == 1 ) {
//                $search_tags = $searchId;
//            var_dump($searchId);die;
                $media = $this->getDoctrine()
                    ->getRepository(Media::class)
                    ->findTagsName($searchId, $limit, $offset);
            $queryParameters['conf'] = $request->get('conf');
            $queryParameters['module'] = $request->get('module');
                $isSearch = true;
        }
//        if($searchGalleryId) {
////            if ($searchId['isgallery'] == 1 ) {
//                $search_galleries = $searchGalleryId;
////                var_dump($search_galleries);die;
//                $media = $this->getDoctrine()
//                    ->getRepository(Media::class)
//                    ->findByGalleries(['search_galleries' => $search_galleries['gallery']], $limit, $offset);
//                $isSearch = true;
////            }
//        }
        $queryParameters = $request->query->all();//var_dump($queryParameters);die;
        $translator = $this->get('translator');
        $isJson = $request->get('json') ? true : false;
        if ($isJson) {
            unset($queryParameters['json']);
        }
        $fileManager = $this->newFileManager($queryParameters);

        // Folder search
        // ToDo should we make it to ask Database
        $directoriesArbo = $this->retrieveSubDirectories($fileManager, $fileManager->getDirName(), DIRECTORY_SEPARATOR, $fileManager->getBaseName());

        // File search
        // ToDo make it to load from the database tables
//        $fileArray = $mediaRepository->findByUrl()]);

        $finderFiles = new Finder();
        $finderFiles->in($fileManager->getCurrentPath())->depth(0);
        $regex = $fileManager->getRegex();

//        $orderBy = $fileManager->getQueryParameter('orderby');
//        $orderDESC = OrderExtension::DESC === $fileManager->getQueryParameter('order');
//        if (!$orderBy) {
//            $finderFiles->sortByType();
//        }

//        switch ($orderBy) {
//            case 'name':
//                $finderFiles->sort(function (SplFileInfo $a, SplFileInfo $b) {
//                    return strcmp(strtolower($b->getFilename()), strtolower($a->getFilename()));
//                });
//                break;
//            case 'date':
//                $finderFiles->sortByModifiedTime();
//                break;
//            case 'size':
//                $finderFiles->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
//                    return $a->getSize() - $b->getSize();
//                });
//                break;
//        }

        if ($fileManager->getTree()) {
            $finderFiles->files()->name($regex)->filter(function (SplFileInfo $file) {
                return $file->isReadable();
            });
        } else {
            $finderFiles->filter(function (SplFileInfo $file) use ($regex) {
                if ('file' === $file->getType()) {
                    if (preg_match($regex, $file->getFilename())) {
                        return $file->isReadable();
                    }

                    return false;
                }

                return $file->isReadable();
            });
        }

        $formDelete = $this->createDeleteForm()->createView();
//        $fileArray = [];
//        $conf = ;
//        $conf = $conf . $fileManager->getCurrentRoute().DIRECTORY_SEPARATOR;
//        $currentPath = $fileManager->getCurrentPath()
        $currentPath = str_replace('\\', '/', $fileManager->getImagePath());
//        var_dump($conf);
//        die();
        if ( !$media  && !$isSearch) {
//            var_dump($currentPath);die;
            $media = $this->mediaRepository->findUrl( $currentPath,$limit, $offset);
        }
//        foreach ($finderFiles as $file) {
//            $fileArray[] = new File($file, $this->get('translator'), $this->get('file_type_service'), $fileManager);
//        }
//        var_dump($fileArray);
//        die();
//        if ('dimension' === $orderBy) {
//            usort($fileArray, function (File $a, File $b) {
//                $aDimension = $a->getDimension();
//                $bDimension = $b->getDimension();
//                if ($aDimension && !$bDimension) {
//                    return 1;
//                }
//
//                if (!$aDimension && $bDimension) {
//                    return -1;
//                }
//
//                if (!$aDimension && !$bDimension) {
//                    return 0;
//                }
//
//                return ($aDimension[0] * $aDimension[1]) - ($bDimension[0] * $bDimension[1]);
//            });
//        }
//
//        if ($orderDESC) {
//            $fileArray = array_reverse($fileArray);
//        }

        $parameters = [
            'fileManager' => $fileManager,
            'media' => $media,
//            'fileArray' => $fileArray,
            'formDelete' => $formDelete,
        ];

        if ($isJson) {
            $fileList = $this->renderView('@AdsignFileManager/views/_manager_view.html.twig', $parameters);

            return new JsonResponse(['data' => $fileList, 'badge' => $finderFiles->count(), 'treeData' => $directoriesArbo]);
        }
        $parameters['treeData'] = json_encode($directoriesArbo);

        $form = $this->get('form.factory')->createNamedBuilder('rename', FormType::class)
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => false,
                'data' => $translator->trans('input.default'),
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => $translator->trans('button.save'),
            ])
            ->getForm();

        /* @var Form $form */
        $form->handleRequest($request);
        /** @var Form $formRename */
        $formRename = $this->createRenameForm();
        $medium = new Media();
        $mediaform = $this->createForm(MediaType::class, $medium);
        $mediaRemoteForm = $this->createForm(MediaRemoteType::class, $medium);
        $mediasearchform = $this->createForm(MediaSearchType::class, $medium);
        $mediagallerysearchform = $this->createForm(MediaGallerySearchType::class, $medium);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fs = new Filesystem();
            $directory = $directorytmp = $fileManager->getCurrentPath().DIRECTORY_SEPARATOR.$data['name'];
            $i = 1;

            while ($fs->exists($directorytmp)) {
                $directorytmp = "{$directory} ({$i})";
                ++$i;
            }
            $directory = $directorytmp;

            try {
                $fs->mkdir($directory);
                $this->addFlash('success', $translator->trans('folder.add.success'));
            } catch (IOExceptionInterface $e) {
                $this->addFlash('danger', $translator->trans('folder.add.danger', ['%message%' => $data['name']]));
            }

            return $this->redirectToRoute('file_manager', $fileManager->getQueryParameters());
        }
        $parameters['form'] = $form->createView();
        $parameters['mediaform'] = $mediaform->createView();
        $parameters['mediaRemoteForm'] = $mediaRemoteForm->createView();
        $parameters['mediasearchform'] = $mediasearchform->createView();
        $parameters['mediagallerysearchform'] = $mediagallerysearchform->createView();
        $parameters['formRename'] = $formRename->createView();
        $parameters['galleries'] = $this->galleryRepository->findAll();


        return $this->render('@AdsignFileManager/manager.html.twig', $parameters);
    }

    /**
     * @Route("/rename/{fileName}", name="file_manager_rename")
     *
     * @param Request $request
     * @param $fileName
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function renameFileAction(Request $request, $fileName)
    {
        $translator = $this->get('translator');
        $queryParameters = $request->query->all();
        $formRename = $this->createRenameForm();
        /* @var Form $formRename */
        $formRename->handleRequest($request);
        if ($formRename->isSubmitted() && $formRename->isValid()) {
            $data = $formRename->getData();
            $extension = $data['extension'] ? '.'.$data['extension'] : '';
            $newfileName = $data['name'].$extension;
            if ($newfileName !== $fileName && isset($data['name'])) {
                $fileManager = $this->newFileManager($queryParameters);
                $NewfilePath = $fileManager->getCurrentPath().DIRECTORY_SEPARATOR.$newfileName;
                $OldfilePath = realpath($fileManager->getCurrentPath().DIRECTORY_SEPARATOR.$fileName);
                if (0 !== strpos($NewfilePath, $fileManager->getCurrentPath())) {
                    $this->addFlash('danger', $translator->trans('file.renamed.unauthorized'));
                } else {
                    $fs = new Filesystem();
                    try {
                        $fs->rename($OldfilePath, $NewfilePath);
                        $this->addFlash('success', $translator->trans('file.renamed.success'));
                        //File has been renamed successfully
                    } catch (IOException $exception) {
                        $this->addFlash('danger', $translator->trans('file.renamed.danger'));
                    }
                }
            } else {
                $this->addFlash('warning', $translator->trans('file.renamed.nochanged'));
            }
        }

        return $this->redirectToRoute('file_manager', $queryParameters);
    }

    /**
     * @Route("/upload/", name="file_manager_upload")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function uploadFileAction(Request $request)
    {
        $this->initialise();

        $fileManager = $this->newFileManager($request->query->all());

        $options = [
            'upload_dir' => $fileManager->getCurrentPath().DIRECTORY_SEPARATOR,
            'upload_url' => $fileManager->getImagePath(),
            'accept_file_types' => $fileManager->getRegex(),
            'print_response' => false,
            'web_dir' => $fileManager->getBasePath()
        ];
        if (isset($fileManager->getConfiguration()['upload'])) {
            $options += $fileManager->getConfiguration()['upload'];
        }

        $this->dispatch(FileManagerEvents::PRE_UPDATE, ['options' => &$options]);


        $medium = new Media();
        $mediaform = $this->createForm(MediaType::class, $medium);
        $mediaform->handleRequest($request);

        if ($mediaform->isSubmitted()) {

//            if(!$mediaform->isValid()) {
//                $error = $mediaform->getErrors();
////                var_dump($error->getChildren());die;
//                return new JsonResponse(array('message' => $error->getChildren()->getMessage() ,'status' => 'error'), Response::HTTP_BAD_REQUEST);
//            }
            // adding media name and tags to list of options passed to uploader
            $options = $options + [
                'prefered_media_name' => $medium->getName().'-'.substr(uniqid(),5),
                'prefered_media_name2' => $medium->getName()
            ];

//        $path = $options['upload_dir'];
//        var_dump($path);
//        die();
        $uploadHandler = new UploadHandler($options);
        $response = $uploadHandler->response;

        foreach ($response['files'] as $file) {
            if (isset($file->error)) {
                $file->error = $this->get('translator')->trans($file->error);
            }
//            var_dump($file);
//            exit();
            $info = pathinfo( $file->name );
//            var_dump($info['size']);
//            exit();
            $medium->setName(strtolower($info['filename']));
            $tag = new Tag();
            $tag->setName(strtolower($options['prefered_media_name2']));
            $medium->addTag($tag);
            $medium->setExtension(strtolower($info['extension']));
            $medium->setType($file->type);
            $explode = explode('/',$file->type,2);
            $medium->setType($explode[0]);

            $medium->setSize($file->size);
            $url = str_replace('\\', '/', $options['upload_url']);
//            var_dump($options);die;
            $medium->setUrl($url);
//            $appPath = $this->container->getParameter('kernel.root_dir');
//            $webPath = realpath($options['upload_dir']);
            if ($explode[0] == 'image') {
                list($width, $height) = getimagesize($options['upload_dir'].$file->name);
                $medium->setDimension($width.'x'.$height);
            }
            $mediaG =$request->request->get('media');
            if (!empty($mediaG['gallery']))
            {
                $galleries = $mediaG['gallery'];
//                var_dump($galleries);die;
                foreach ($galleries as $galleryName)
                {
                    $galleryName = str_replace('__','',$galleryName);
                    $gallery = $this->galleryRepository->findOneBy(['name' => $galleryName]);
                    if($gallery)
//                        $medium->addGallery($gallery);
                        $gallery->addMedia($medium);
                else {
                        $gallery = new Gallery();
                        $gallery->setName(str_replace('__','',$galleryName));
                        $gallery->setType($explode[0]);
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($gallery);
                        $em->flush();

//                        var_dump($gallery);die;

//                        $medium->addGallery($gallery);
                        $gallery->addMedia($medium);

//                        var_dump($medium);die;
                    }
                }
            }
//
//            $encoders = array(new XmlEncoder(), new JsonEncoder());
//            $normalizers = array(new ObjectNormalizer());
//            $serializer = new Serializer($normalizers, $encoders);
//            $medium->setRotags($serializer->serialize($medium->getTags(), 'json'));
//
//            $medium->setPath($options['upload_dir']);
//            $medium->setThumbnailUrl($file->thumbnailUrl);
            $em = $this->getDoctrine()->getManager();
            $em->persist($medium);
            $em->persist($tag);
//            var_dump($medium->getGallery());
//            die;
            $em->flush();
            }

            if (!$fileManager->getImagePath()) {
                $file->url = $this->generateUrl('file_manager_file', array_merge($fileManager->getQueryParameters(), ['fileName' => $file->url]));
            }
        }

        $this->dispatch(FileManagerEvents::POST_UPDATE, ['response' => &$response]);

        return new JsonResponse($response);
    }

    /**
     * @Route("/remote-upload/", name="file_manager_remote_upload")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function remoteUploadFileAction(Request $request)
    {
        $this->initialise();

        $fileManager = $this->newFileManager($request->query->all());
//
        $options = [
            'upload_dir' => $fileManager->getCurrentPath().DIRECTORY_SEPARATOR,
            'upload_url' => $fileManager->getImagePath(),
            'accept_file_types' => $fileManager->getRegex(),
            'print_response' => false,
        ];
//        var_dump($request->request->get('name'));die;


//        if (isset($fileManager->getConfiguration()['upload'])) {
//            $options += $fileManager->getConfiguration()['upload'];
//        }

//        $this->dispatch(FileManagerEvents::PRE_UPDATE, ['options' => &$options]);


        $medium = new Media();
//        $mediaRemoteForm = $this->createForm(MediaRemoteType::class, $medium);
//        $mediaRemoteForm->handleRequest($request);
//
//        $mediaRemoteForm->isSubmitted(); $mediaRemoteForm->isValid();

            set_time_limit(-1);
            $ch = curl_init();
            if(!empty($request->request->get('url')))
            {
                $url = $request->request->get('url');
            } else {
                return new JsonResponse(array('message' => $this->get('translator')->trans('url could not be empty!'),'status' => 'error'), Response::HTTP_OK);
            }
            if(!empty($request->request->get('name')))
            {
                $name = $request->request->get('name').'-'.substr(uniqid(),5);
            } else {
                return new JsonResponse(array('message' => $this->get('translator')->trans('name could not be empty!'),'status' => 'error'), Response::HTTP_OK);
            }

            $outfileh=tmpfile();
//            $outfile=stream_get_meta_data($outfileh)['uri'];

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
            curl_setopt($ch, CURLOPT_TIMEOUT, 50);
//            curl_setopt($ch,CURLOPT_FILE,$outfileh);

//        curl_setopt($ch, CURLOPT_SSLVERSION,3);

//        $chresult = curl_exec($ch);
//        $data=file_get_contents($outfile);
        $data = curl_exec ($ch);
        $error = curl_error($ch);
        curl_close ($ch);

//        if ($error)
//        {
//            $this->addFlash(
//                'error',
//                'خطا در آپلود فایل'
//            );
//            return $this->redirectToRoute('file_manager');
//        }

        $date = new \DateTime('');
        $date1 = uniqid() . '--' . $date->format('Y-m-d-H-i-s');
        $destination = $options['upload_dir'].'/temp'. $date1;
        $file = fopen($destination, "wb");
        fwrite($file, $data);
        fclose($file);
//        var_dump($data);die;
        $file = new \Symfony\Component\HttpFoundation\File\File($destination,1);
//        dump($file);
//        die();
        $upload_file = $this->uploadFile(
            $file,
            $options['upload_dir'] ,
            null,
            $name);
        $file_upload_status = $upload_file['result'];
        if ($file_upload_status === false)
        {
            unlink($destination );
            return new JsonResponse(array('message' => $upload_file['message'],'status' => 'error'), Response::HTTP_OK);
        }
        $urlFile = $upload_file['message'];
//            $logoName = $urlFile['fileName'];
            $explode = explode('.',$urlFile,2);
//            dump($urlFile);
//            die();
        $file = new \Symfony\Component\HttpFoundation\File\File($options['upload_dir'].$urlFile);
        $medium->setName(strtolower($explode[0]));
            // adding media name and tags to list of options passed to uploader
//            $options = $options + [
//                    'prefered_media_name' => $medium->getName()
//                ];
//        }
//        $path = $options['upload_dir'];
//        var_dump($path);
//        die();
//        $uploadHandler = new UploadHandler($options);
//        $response = $uploadHandler->response;

//        foreach ($response['files'] as $file) {
//            if (isset($file->error)) {
//                $file->error = $this->get('translator')->trans($file->error);
//            }
//            var_dump($urlFile);
//            exit();
//            $info = pathinfo( $urlFile->name );
//            var_dump($info);
//            exit();
//            $medium->setName(strtolower($info['filename']));
            $medium->setExtension(strtolower($explode[1]));
//            $medium->setType($file->type);
            $MimeType = explode('/',$file->getMimeType(),2);
            $medium->setType($MimeType[0]);
            if ($MimeType[0] == 'image') {
                list($width, $height) = getimagesize($options['upload_dir'].$urlFile);
                $medium->setDimension($width.'x'.$height);
            }
            $medium->setSize($file->getSize());
            $url = str_replace('\\', '/', $options['upload_url']);
            $medium->setUrl($url);
            $appPath = $this->container->getParameter('kernel.root_dir');
            if (!empty($request->request->get('tag')))
            {
                $tags = $request->request->get('tag');
                foreach ($tags as $tagName)
                {
                    $tagName = str_replace('__','',$tagName);
                    $tag = $this->tagRepository->findOneBy(['name' => $tagName]);
                    if($tag)
                        $medium->addTag($tag);
                    else {
                        $tag = new Tag();
                        $tag->setName(str_replace('__','',$tagName));
                        $medium->addTag($tag);
                    }
                }
            }

            if (!empty($request->request->get('gallery')))
            {
                $galleries = $request->request->get('gallery');
                foreach ($galleries as $galleryName)
                {
                    $galleryName = str_replace('__','',$galleryName);
                    $gallery = $this->galleryRepository->findOneBy(['name' => $galleryName]);
                    if($gallery)
                        $medium->addGallery($gallery);
                    else {
                        $gallery = new Gallery();
                        $gallery->setName(str_replace('__','',$galleryName));
                        $gallery->setType($MimeType[0]);
                        $medium->addGallery($gallery);
                    }
                }
            }

        $webPath = realpath($options['upload_dir']);
//            if ($MimeType[0] == 'image') {
//                list($width, $height) = getimagesize($webPath.$url.$file->name);
//                $medium->setDimension($width.'x'.$height);
//            }
//
//            $encoders = array(new XmlEncoder(), new JsonEncoder());
//            $normalizers = array(new ObjectNormalizer());
//            $serializer = new Serializer($normalizers, $encoders);
//            $medium->setRotags($serializer->serialize($medium->getTags(), 'json'));
//
//            $medium->setPath($options['upload_dir']);
//            $medium->setThumbnailUrl($file->thumbnailUrl);
            $em = $this->getDoctrine()->getManager();
            $em->persist($medium);
            $em->flush();

//            if (!$fileManager->getImagePath()) {
//                $file->url = $this->generateUrl('file_manager_file', array_merge($fileManager->getQueryParameters(), ['fileName' => $file->url]));
//            }
//        }

//        $this->dispatch(FileManagerEvents::POST_UPDATE, ['response' => &$response]);
        return new JsonResponse(array('status' => 'success','message' => $this->get('translator')->trans('file Uploaded!')), Response::HTTP_OK);

    }

    /**
     * @Route("/file/{fileName}", name="file_manager_file")
     *
     * @param Request $request
     * @param $fileName
     *
     * @return BinaryFileResponse
     *
     * @throws \Exception
     */
    public function binaryFileResponseAction(Request $request, $fileName)
    {
        $fileManager = $this->newFileManager($request->query->all());

        return new BinaryFileResponse($fileManager->getCurrentPath().DIRECTORY_SEPARATOR.urldecode($fileName));
    }

    /**
     * @Route("/delete/", name="file_manager_delete")
     *
     * @param Request $request
     * @Method("DELETE")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Exception
     */
    public function deleteAction(Request $request)
    {
        $this->initialise();
        $form = $this->createDeleteForm();
        $form->handleRequest($request);
        $queryParameters = $request->query->all();
        if ($form->isSubmitted() && $form->isValid()) {
            // remove file
            $fileManager = $this->newFileManager($queryParameters);
            $fs = new Filesystem();
            if (isset($queryParameters['delete'])) {
                $is_delete = false;
                foreach ($queryParameters['delete'] as $fileName) {
                    $filePath = realpath($fileManager->getCurrentPath().DIRECTORY_SEPARATOR.$fileName);
                    if (0 !== strpos($filePath, $fileManager->getCurrentPath())) {
                        $this->addFlash('danger', 'file.deleted.danger');
                        //ToDo check to see if we are deleting in correct place
//                        $mediaName = $this->mediaRepository->findOneBy(['name' => $fileName]);
//                        $em = $this->getDoctrine()->getManager();
//                        $em->remove($mediaName);
//                        $em->flush();
                    } else {
                        $this->dispatch(FileManagerEvents::PRE_DELETE_FILE);
                        try {
                            $mediaName = $this->mediaRepository->findOneBy(['name' => $fileName]);
                            $em = $this->getDoctrine()->getManager();
                            $em->remove($mediaName);
                            $em->flush();
                            $fs->remove($filePath);
                            $is_delete = true;
                        } catch (IOException $exception) {
                            $this->addFlash('danger', 'file.deleted.unauthorized');
                        }
                        $this->dispatch(FileManagerEvents::POST_DELETE_FILE);
                    }
                }
                if ($is_delete) {
                    $this->addFlash('success', 'file.deleted.success');
                }
                unset($queryParameters['delete']);
            } else {
                $this->dispatch(FileManagerEvents::PRE_DELETE_FOLDER);
                try {
                    $fs->remove($fileManager->getCurrentPath());
                    $this->addFlash('success', 'folder.deleted.success');
                } catch (IOException $exception) {
                    $this->addFlash('danger', 'folder.deleted.unauthorized');
                }

                $this->dispatch(FileManagerEvents::POST_DELETE_FOLDER);
                $queryParameters['route'] = dirname($fileManager->getCurrentRoute());
                if ($queryParameters['route'] = '/') {
                    unset($queryParameters['route']);
                }

                return $this->redirectToRoute('file_manager', $queryParameters);
            }
        }

        return $this->redirectToRoute('file_manager', $queryParameters);
    }

    /**
     * @return Form|\Symfony\Component\Form\FormInterface
     */
    private function createDeleteForm()
    {
        return $this->createFormBuilder()
            ->setMethod('DELETE')
            ->add('DELETE', SubmitType::class, [
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
                'label' => 'button.delete.action',
            ])
            ->getForm();
    }

    /**
     * @return mixed
     */
    private function createRenameForm()
    {
        return $this->createFormBuilder()
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => false,
            ])->add('extension', HiddenType::class)
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
                'label' => 'button.rename.action',
            ])
            ->getForm();
    }

    /**
     * @param FileManager $fileManager
     * @param $path
     * @param string $parent
     * @param bool   $baseFolderName
     *
     * @return array|null
     */
    private function retrieveSubDirectories(FileManager $fileManager, $path, $parent = DIRECTORY_SEPARATOR, $baseFolderName = false)
    {
        $directories = new Finder();
        $directories->in($path)->ignoreUnreadableDirs()->directories()->depth(0)->sortByType()->filter(function (SplFileInfo $file) {
            return $file->isReadable();
        });

        if ($baseFolderName) {
            $directories->name($baseFolderName);
        }
        $directoriesList = null;

        foreach ($directories as $directory) {
            /** @var SplFileInfo $directory */
            $fileName = $baseFolderName ? '' : $parent.$directory->getFilename();

            $queryParameters = $fileManager->getQueryParameters();
            $queryParameters['route'] = $fileName;
            $queryParametersRoute = $queryParameters;
            unset($queryParametersRoute['route']);

//            $filesNumber = $this->retrieveFilesNumber($directory->getPathname(), $fileManager->getRegex());
//            $fileSpan = $filesNumber > 0 ? " <span class='label label-default'>{$filesNumber}</span>" : '';

            $directoriesList[] = [
                'text' => $directory->getFilename(),//.$fileSpan,
                'icon' => 'far fa-folder-open',
                'children' => $this->retrieveSubDirectories($fileManager, $directory->getPathname(), $fileName.DIRECTORY_SEPARATOR),
                'a_attr' => [
                    'href' => $fileName ? $this->generateUrl('file_manager', $queryParameters) : $this->generateUrl('file_manager', $queryParametersRoute),
                ], 'state' => [
                    'selected' => $fileManager->getCurrentRoute() === $fileName,
                    'opened' => true,
                ],
            ];
        }

        return $directoriesList;
    }

    /**
     * Tree Iterator.
     *
     * @param $path
     * @param $regex
     *
     * @return int
     */
    private function retrieveFilesNumber($path, $regex)
    {
        $files = new Finder();
        $files->in($path)->files()->depth(0)->name($regex);

        return iterator_count($files);
    }

    /*
     * Base Path
     */
    private function getBasePath($queryParameters)
    {
        $conf = $queryParameters['conf'];
        $managerConf = $this->getParameter('fara_data_file_manager')['conf'];
        if (isset($managerConf[$conf]['dir'])) {
            return $managerConf[$conf];
        }

        if (isset($managerConf[$conf]['service'])) {
            $extra = isset($queryParameters['extra']) ? $queryParameters['extra'] : [];
            $conf = $this->get($managerConf[$conf]['service'])->getConf($extra);

            return $conf;
        }

        throw new \RuntimeException('Please define a "dir" or a "service" parameter in your config.yml');
    }

    /**
     * @return mixed
     */
    private function getKernelRoute()
    {
        return $this->getParameter('kernel.root_dir');
    }

    /**
     * @param $queryParameters
     *
     * @return FileManager
     *
     * @throws \Exception
     */
    private function newFileManager($queryParameters)
    {
        if (!isset($queryParameters['conf'])) {
            throw new \RuntimeException('Please define a conf parameter in your route');
        }
        $webDir = $this->getParameter('fara_data_file_manager')['web_dir'];

        $this->fileManager = new FileManager($queryParameters, $this->getBasePath($queryParameters), $this->getKernelRoute(), $this->get('router'), $webDir);

        return $this->fileManager;
    }

    protected function dispatch($eventName, array $arguments = [])
    {
        $arguments = array_replace([
            'filemanager' => $this->fileManager,
        ], $arguments);

        $subject = $arguments['filemanager'];
        $event = new GenericEvent($subject, $arguments);
        $this->get('event_dispatcher')->dispatch($eventName, $event);
    }

    /**
     * @param $file
     * @param string $destination
     * @param string $name
     * @param array $mimeTypesArray
     * @param array $formatsArray
     * @param int $maxFileSize
     * @param null $thumbDestination
     * @param int $width
     * @param int $height
     * @return array
     */
    private function uploadFile(
        $file,
        $destination = '/uploads',
        $thumbDestination = null,
        $name = null,
        $mimeTypesArray = ['image/png', 'image/jpg', 'image/jpeg', 'video/mp4' , 'audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg', 'audio/wav', 'audio/x-wav', 'video/mpeg', 'video/quicktime'],
        $formatsArray = ['jpg','jpeg','png','mp3', 'mp4'],
        $maxFileSize = 874886990,
        $width = 300,
        $height = 250
    )
    {
        $mimeTypes = $mimeTypesArray;
        $filew = $file->getMimeType();
        if (!in_array($filew, $mimeTypes)) {

            $txt = 'فرمت فایل ارسالی باید یکی از فرمت های ';
            for ($i = 0; $i < count($formatsArray); $i++) {
                if ($i != (count($formatsArray) - 1)) {
                    $txt .= $formatsArray[$i] . ' یا ';
                } else {
                    $txt .= $formatsArray[$i];
                }
            }
            $txt .= ' باشد.';
            return array(
                'result' => false,
                'message' => $txt
            );
        }

//        if (!$file->isValid()) {
//            return array(
//                'result' => false,
//                'message' => 'فایل ارسال شده نامعتبر است'
//            );
//        }
//
//
//        if ($file->getClientSize() > $maxFileSize) {
//            return array(
//                'result' => false,
//                'message' => 'حجم فایل باید کمتر از ' . $maxFileSize . ' بایت باشد'
//            );
//        }
//        echo var_dump($file->getClientSize() > 0);
//        die;
//        if ($file->getClientSize() > 0) {
//            $date = new \DateTime('');
//            $date = $date->format('Y-m-d-H-i-s');
            if($name)
            {
                $name = stripslashes($name);
                $name = $name . '.'. $file->guessExtension();
            } else {
                $name = uniqid() . '.' . $file->guessExtension();
            }

            try {
                $origFile = $file->move($destination, $name);
//                if ($thumbDestination) {
//                    $ri = new ResizeImage($origFile->getPathName());
//                    $ri->resizeTo($width, $height , 'maxwidth');
//                    $ri->saveImage($thumbDestination . '/' . $name);
//                }
                return array(
                    'result' => true,
                    'message' => $name,
//                    'size' => $file->getClientSize(),
                );
            } catch (FileException $e) {
                return array(
                    'result' => false,
                    'message' => 'خطا در بارگذاری فایل، لطفا مجددا تلاش کنید'
                );
            }
        }
//    }
}
