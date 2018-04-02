<?php

namespace Top10\CabinetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class wikiForm extends AbstractType
{
	protected $wiki;

    public function __construct($wiki)
    {
        $this->wiki = $wiki;
    }


	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$wiki = $this->wiki;

		$choices_type = array(
			'checkbox' => 'галочка',
			'tree' => 'плюсик',
			'caption' => 'заголовок',
		);

		if( $wiki->getCode() == null )
			$code = array( 'label' => 'ЧПУ', 'empty_data' => $wiki->getCode(), "required" => false, );

		$name = array( 'label' => 'Название', 'empty_data' => $wiki->getName(), "required" => true, 'attr' => array('placeholder' => 'Название') );
		$image = array( 'label' => 'Картинка', 'empty_data' => $wiki->getImage(), "required" => false, );
		$content = array( 'label' => 'Описание', 'empty_data' => $wiki->getContent(), "required" => false,  );


		//if( $wiki->getApproved() )
			//$approved['attr'] = array('checked' => 'checked');

		if( $wiki->getCode() == null )
			$builder->add( 'code', 'text', $code );

		$builder
			->add( 'name', 'text', $name )
			->add( 'image', 'text', $image )
			->add( 'content', 'textarea', $content );

	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Top10\CabinetBundle\Entity\wiki'
        ));
    }

    public function getName()
    {
        return 'top10_cabinetbundle_wiki';
    }
}
