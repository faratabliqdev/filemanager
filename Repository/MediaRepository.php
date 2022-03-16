<?php

namespace Adsign\FileManagerBundle\Repository;

use Adsign\FileManagerBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends EntityRepository
{
    // public function __construct(RegistryInterface $registry)
    // {
    //     parent::__construct($registry, Media::class);
    // }

    public function likeMedia($searchString)
    {
        $fields = array('m.id, m.name AS text');
        return $this->createQueryBuilder('m')
            ->select($fields)
            ->where('m.name LIKE :searchString')
            ->setParameter('searchString', '%'.$searchString.'%')
            ->getQuery()
            ->getResult()
            ;
    }

//    /**
//     * @return Media[] Returns an array of Media objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Media
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findUrl($value, $limit, $offset)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.url = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByTags($tagIds, $limit, $offset)
    {
        $query = $this->createQueryBuilder('m')
            ->innerJoin('m.tag', 't');
        foreach ($tagIds['search_tags'] as $tagId) {
//            var_dump($tagId);die;
            $query = $query
                ->orWhere('t.id = :val')
                ->setParameter('val', $tagId);
        }
        return $query
            ->orderBy('m.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
//        $count = count($tagIds) + 1;
////        var_dump($tagIds['search_tags'][0]);
////        die();
//        $sql = 'SELECT
//            *
//            FROM
//            fm_media
//            INNER JOIN fm_media_tag md ON md.media_id = fm_media.id
//            WHERE
//                 md.tag_id IN(';
//        for($i=0; $i<$count;$i++){
////            var_dump(json_encode($tagIds));
////                die();
//            $sql = $sql. $tagIds['search_tags'][$i];
//            if ($i < $count - 1) $sql = $sql.',';
//        }
////        var_dump($sql);
////        die();
//        $sql = $sql. ')
//            GROUP BY md.media_id
//            HAVING COUNT(md.tag_id) = '. $count;
////        var_dump($sql);
////        die();
//        $conn = $this->getEntityManager()->getConnection();
//
////        var_dump($conn);
////        die();
//
//        $stmt = $conn->prepare($sql);
//        $stmt->execute();
////        var_dump($stmt->fetchAll());
////        die();
//        // returns an array of arrays (i.e. a raw data set)
//        return $stmt->fetchAll();
    }
    public function findByGalleries($galleryIds, $limit, $offset)
    {
//        var_dump($galleryIds);die;
        $query = $this->createQueryBuilder('m')
            ->innerJoin('m.gallery', 'g');
        foreach ($galleryIds['search_galleries'] as $galleryId) {
//            var_dump($tagId);die;
            $query = $query
                ->orWhere('g.id = :val')
                ->setParameter('val',$galleryId);
        }
        return $query
            ->orderBy('m.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function findTagsName($searchId, $limit, $offset)
    {
        //$searchId = '%'.$searchId.'%';
//        var_dump($searchId);die;
        return $this->createQueryBuilder('m')
            ->innerJoin('m.tag', 't')
            ->andWhere('m.name like :val or t.name like :val')
            ->setParameter('val','%'.$searchId.'%')
            ->orderBy('m.name', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
