<?php
namespace Top10\CabinetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

//use JMS\SecurityExtraBundle\Annotation\Secure;
//use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Top10\CabinetBundle\Entity;
use Top10\CabinetBundle\Entity\Wiki;

use Top10\CabinetBundle\Form\wikiForm;
use Knp\Component\Pager\Paginator;


/**
 * @Route("/")
 */
class WikiController extends Controller
{

	/**
     * Edit a wiki.
     *
     * @Route("/wiki", name="wiki")
     * @Template("Top10CabinetBundle:Wiki:index.html.twig")
     */
	public function indexAction( Request $request)
	{
		$em 			= $this->getDoctrine()->getManager();
		$repWiki		= $em->getRepository('Top10CabinetBundle:Wiki');

		$arrWikiForeginY	   = array();
		$arrWikiForeginParentY = array();

		$wiki = $repWiki
			->createQueryBuilder('w')
			->select('w')
			->andWhere( 'w.parent IS NULL' )
			->getQuery()
			->getResult();

		$result = array(
			'wiki' => $wiki,
		);

		return $result;
	}

	/**
     * Show a wiki.
     *
     * @Route("/wiki/{code}/", name="wiki_show")
     * @Template("Top10CabinetBundle:Wiki:showwiki.html.twig")
     */
    public function wikiShowAction( $code, Request $request)
    {
		$security 		= $this->get('security.context');
		$current_user	= $security->getToken()->getUser();
		$router 		= $this->get('router'); 
		$breadcrumbs	= $this->get('white_october_breadcrumbs');
		$wikiTextToHtml = $this->get('cabinet.wiki_text_to_html');
		$em 			= $this->getDoctrine()->getManager();
		

		/** @var $wiki wikis */
		$rep = $em->getRepository('Top10CabinetBundle:Wiki');

		$wiki = $rep->findOneByCode($code);

		if( !$wiki )
			throw $this->createNotFoundException('Not found wiki entity.');

		//--------хлебные крошки----------------------//
		$breadcrumbs->addItem(
			'Список страниц',
			$router->generate('wiki')
		);


		$breadcrumbs->addItem( $wiki->getName() );
		//--------/хлебные крошки----------------------//

		//--------преобразуем Wiki в HTML----------------------//
		if( $wiki->getContent() != null ){

			// parse and display input
			$wikitextArr = explode(
				"\n",
				trim(
					stripslashes(
						$wiki->getContent()
					)
				)
			);


			// convert input to HTML output array
			$output = $wikiTextToHtml->convertWikiTextToHTML( $wikitextArr );


			// output to stream with newlines
			$content = null;
			foreach( $output as $line ) {
				$content .= "${line}\n";
			}
		}
		//--------/преобразуем Wiki в HTML----------------------//
		
		
		$result = array(
			'wiki' => $wiki,
			'wikiСontent' => $content,
		);

		return $result;
	}


	/**
     * Show a wiki.
     *
     * @Route("/wiki/{code}/edit", name="wiki_edit")
     * @Template("Top10CabinetBundle:Wiki:edit.html.twig")
     */
	public function wikiEditAction( $code, Request $request)
	{
		//$security				= $this->get('security.context');
		//$current_user			= $security->getToken()->getUser();

		$wikiTextToHtml 		= $this->get('cabinet.wiki_text_to_html');
		/** @var $postSearch \Top10\CabinetBundle\Service\TranslateChar */
		$em 					= $this->getDoctrine()->getManager();
		$repWiki				= $em->getRepository('Top10CabinetBundle:Wiki');

		if( $code ){
			$wiki = $repWiki->findOneByCode( $code );
			if(!$wiki)
				$wiki = new wiki;
		}
		else{
			$wiki = new wiki;
		}


		//--------хлебные крошки----------------------//
		$router 			= $this->get('router'); 
		$breadcrumbs		= $this->get('white_october_breadcrumbs');

		$breadcrumbs->addItem( 
			$wiki->getName(),
			$router->generate( 'wiki_show', array( 'code' => $wiki->getCode() ) )
		);

		$breadcrumbs->addItem( 'Редактировать страницу ');
		//--------/хлебные крошки----------------------//


		$wikiForm = $this->createForm( new wikiForm( $wiki ), $wiki );

		$wikiForm->bind($request);

		if ( $request->isMethod('POST') /*&& $wikiForm->isValid()*/ ) {

			$em->persist( $wiki );
			$em->flush();

			return $this->redirect( $this->generateUrl( 'wiki_show', array( 'code' => $wiki->getCode() ) ) );

		}

		$result = array(
			'wikiForm' => $wikiForm->createView(),
			'wiki' => $wiki,
		);

		return $result;

	}

	/**
     * Show a wiki.
     *
     * @Route("/wiki/{code_parent}/add/", name="wiki_children_add")
     * @Template("Top10CabinetBundle:Wiki:add.html.twig")
     */
	public function wikiChildrenAddAction( $code_parent, Request $request)
	{
		return $this->wikiCreateAction($code_parent);
	}

	/**
     * Show a wiki.
     *
     * @Route("/wikiadd/", name="wiki_add")
     * @Template("Top10CabinetBundle:Wiki:add.html.twig")
     */
	public function wikiAddAction()
	{
		return $this->wikiCreateAction(null);
	}

	public function wikiCreateAction( $code_parent )
	{
		$request 	= $this->getRequest();
		$em 		   = $this->getDoctrine()->getManager();
		$translateChar = $this->get('cabinet.translate_char');

		if( $code_parent != null ){
			$repWikiParent 	= $em->getRepository('Top10CabinetBundle:Wiki');
			$wikiParent 	= $repWikiParent->findOneByCode( $code_parent );
		}
		else
			$wikiParent = false;

		$wiki = new wiki;

		//--------хлебные крошки----------------------//
		$router 			= $this->get('router'); 
		$breadcrumbs		= $this->get('white_october_breadcrumbs');

		if($wikiParent)
			$breadcrumbs->addItem( 
				$wikiParent->getName(),
				$router->generate( 'wiki_show', array( 'code' => $wikiParent->getCode() ) )
			);

		$breadcrumbs->addItem( 'Добавить дочернюю страницу ');
		//--------/хлебные крошки----------------------//

		$wikiForm = $this->createForm( new wikiForm( $wiki ), $wiki );

		$wikiForm->bind($request);

		if ( $request->isMethod('POST') /*&& $wikiForm->isValid()*/ ) {

			if($wikiParent)
				$wiki->setParent( $wikiParent );
			
			if( $wikiForm->get('code')->getData() == null ){
				$wiki->setCode( 
					preg_replace('#[^0-9A-Za-z_-]+# ', '', 
						strtolower(
							$translateChar->getInTranslateToEn(
								$wikiForm->get('name')->getData(),
								true 
							) 
						) 
					)
				);
			}

			$em->persist( $wiki );
			$em->flush();

			return $this->redirect( $this->generateUrl( 'wiki_show', array( 'code' => $wiki->getCode() ) ) );

		}

		$result = array(
			'wikiForm' => $wikiForm->createView(),
			'wikiParent' => $wikiParent,
		);

		return $result;

	}

	/**
     * Delete a wiki.
     *
	 * @Route("/wiki/{code}/delete", name="wiki_delete")
    */
    public function wikiDeleteAction($code)
	{
		$security = $this->get('security.context');

		if ( !$security->isGranted('ROLE_ADMIN') )
			throw $this->createNotFoundException('Not found wiki entity.');

		$em = $this->getDoctrine()->getManager();
		$repWiki = $em->getRepository('Top10CabinetBundle:Wiki');

		$wiki = $repWiki->findOneByCode( $code );

		$postSecurity = $this->get('cabinet.security_role')->getObject( $repWiki, $wiki->getId() );

		if( $wiki->getParent()->getId() );
			$parentCode = $wiki->getParent()->getCode();

		$em->remove($wiki);
		$em->flush();

		if( $parentCode )
			return $this->redirect( $this->generateUrl( 'wiki_show', array( 'code' => $parentCode ) ) );
		else
			return $this->redirect( $this->generateUrl( 'wiki' ) );
	}

}