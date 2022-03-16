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
 * Class TagDatatable
 *
 * @package Adsign\FileManagerBundle\Datatables
 */
class TagDatatable extends AbstractDatatable
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
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order_cells_top' => true,
        ));

        $this->features->set(array(
        ));

        $this->columnBuilder
            ->add('id', Column::class, array(
                'title' => 'Id',
                ))
            ->add('name', Column::class, array(
                'title' => 'Name',
                ))
            ->add('count', Column::class, array(
                'title' => 'Count',
                ))
            ->add('createdAt', DateTimeColumn::class, array(
                'title' => 'CreatedAt',
                ))
            ->add('media.id', Column::class, array(
                'title' => 'Media Id',
                'data' => 'media[, ].id'
                ))
            ->add('media.name', Column::class, array(
                'title' => 'Media Name',
                'data' => 'media[, ].name'
                ))
            ->add('media.extension', Column::class, array(
                'title' => 'Media Extension',
                'data' => 'media[, ].extension'
                ))
            ->add('media.type', Column::class, array(
                'title' => 'Media Type',
                'data' => 'media[, ].type'
                ))
            ->add('media.size', Column::class, array(
                'title' => 'Media Size',
                'data' => 'media[, ].size'
                ))
            ->add('media.url', Column::class, array(
                'title' => 'Media Url',
                'data' => 'media[, ].url'
                ))
            ->add('media.dimension', Column::class, array(
                'title' => 'Media Dimension',
                'data' => 'media[, ].dimension'
                ))
            ->add('media.createdAt', Column::class, array(
                'title' => 'Media CreatedAt',
                'data' => 'media[, ].createdAt'
                ))
            ->add(null, ActionColumn::class, array(
                'title' => $this->translator->trans('sg.datatables.actions.title'),
                'actions' => array(
                    array(
                        'route' => 'file_manager_tag_show',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => $this->translator->trans('sg.datatables.actions.show'),
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => $this->translator->trans('sg.datatables.actions.show'),
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                    ),
                    array(
                        'route' => 'file_manager_tag_edit',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => $this->translator->trans('sg.datatables.actions.edit'),
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => $this->translator->trans('sg.datatables.actions.edit'),
                            'class' => 'btn btn-primary btn-xs',
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
        return 'Adsign\FileManagerBundle\Entity\Tag';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tag_datatable';
    }
}
