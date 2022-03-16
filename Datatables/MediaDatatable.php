<?php

namespace Adsign\FileManagerBundle\Datatables;

use Sg\DatatablesBundle\Datatable\AbstractDatatable;
use Sg\DatatablesBundle\Datatable\Style;
use Sg\DatatablesBundle\Datatable\Column\Column;
use Sg\DatatablesBundle\Datatable\Column\BooleanColumn;
use Sg\DatatablesBundle\Datatable\Column\ActionColumn;
use Sg\DatatablesBundle\Datatable\Column\MultiselectColumn;
use Sg\DatatablesBundle\Datatable\Column\VirtualColumn;
use Sg\DatatablesBundle\Datatable\Column\DateTimeColumn;
use Sg\DatatablesBundle\Datatable\Column\ImageColumn;
use Sg\DatatablesBundle\Datatable\Filter\TextFilter;
use Sg\DatatablesBundle\Datatable\Filter\NumberFilter;
use Sg\DatatablesBundle\Datatable\Filter\SelectFilter;
use Sg\DatatablesBundle\Datatable\Filter\DateRangeFilter;
use Sg\DatatablesBundle\Datatable\Editable\CombodateEditable;
use Sg\DatatablesBundle\Datatable\Editable\SelectEditable;
use Sg\DatatablesBundle\Datatable\Editable\TextareaEditable;
use Sg\DatatablesBundle\Datatable\Editable\TextEditable;

/**
 * Class MediaDatatable
 *
 * @package Adsign\FileManagerBundle\Datatables
 */
class MediaDatatable extends AbstractDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable(array $options = array())
    {
        $this->language->set(array(
            'cdn_language_by_locale' => true
            //'language' => 'de'
        ));

        $this->ajax->set(array(
        ));

        $this->options->set(array(
            'classes' => Style::BOOTSTRAP_3_STYLE,
            'stripe_classes' => [ 'strip1', 'strip2', 'strip3' ],
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
            'order' => array(array(0, 'desc')),
        ));

        $this->features->set(array(
        ));

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'ردیف',
                'searchable' => true,
                'orderable' => true,
                'width' => '30px',
                'filter' => array(NumberFilter::class, array(
//                    'classes' => 'test1 test2',
                    'search_type' => 'eq',
//                    'cancel_button' => true,
                    'type' => 'number',
                    'show_label' => true,
//                    'datalist' => array('3', '50', '75')
                )),
            ))
            ->add('name', Column::class, array(
                'title' => 'عنوان',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '150px',
            ))
            ->add('extension', Column::class, array(
                'title' => 'پسوند',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '50px',
                ))
            ->add('type', Column::class, array(
                'title' => 'نوع',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '50px',
                ))
            ->add('size', Column::class, array(
                'title' => 'اندازه',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '50px',
                ))
//            ->add('url', Column::class, array(
//                'title' => 'آدرس',
//                'searchable' => true,
//                'orderable' => true,
//                'default_content' => 'ندارد',
//                'width' => '150px',
//                ))
            ->add('dimension', Column::class, array(
                'title' => 'ابعاد',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '50px',
                ))
            ->add('createdAt', DateTimeColumn::class, array(
                'title' => 'تاریخ ایجاد',
                'default_content' => 'ندارد',
                'date_format'     => 'DD-MM-YYYY',
                'date_format' => 'L',
                'width' => '150px',
//                'searchable' => false,
                'filter' => array(DateRangeFilter::class, array(
//                    'cancel_button' => true,
                )),
//                'editable' => array(CombodateEditable::class, array(
//                    'format' => 'YYYY-MM-DD',
//                    'view_format' => 'DD.MM.YYYY',
//                    //'pk' => 'cid'
//                )),
//                'timeago' => true
            ))
//            ->add('tag.id', Column::class, array(
//                'title' => 'Tag Id',
//                'data' => 'tag[, ].id'
//                ))
            ->add('tag.name', Column::class, array(
                'title' => 'برچسب ها',
                'data' => 'tag[, ].name',
                'searchable' => true,
                'orderable' => true,
                'default_content' => 'ندارد',
                'width' => '150px',
                ))
//            ->add('tag.count', Column::class, array(
//                'title' => 'Tag Count',
//                'data' => 'tag[, ].count'
//                ))
//            ->add('tag.createdAt', Column::class, array(
//                'title' => 'Tag CreatedAt',
//                'data' => 'tag[, ].createdAt'
//                ))
            ->add(null, ActionColumn::class, array(
                'title' => 'اقدامات',
                'actions' => array(
                    array(
                        'route' => 'file_manager_media_show',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'نمایش',
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'نمایش',
                            'class' => 'btn btn-info btn-icon',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'file_manager_media_edit',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'ویرایش',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'ویرایش',
                            'class' => 'btn btn-orange btn-icon',
                            'role' => 'button'
                        ),
                    )
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Adsign\FileManagerBundle\Entity\Media';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media_datatable';
    }
}
