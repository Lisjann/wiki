<?php
namespace Top10\CabinetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WikiTextToHTML {
	/**
	 * Wiki text to HTML script.
	 * (c) 2007, Frank Schoep
	 *
	 * This script will convert the input given on the standard input
	 * from Wiki style text syntax to minimally formatted HTML output.
	 */

	// these constants define the list states
	/*public 0		=	0;
	public 1	=	1;
	public 2	=	2;*/


	/**
	* @var ContainerInterface $container
	*/
	protected $container;

	// definitions for the list open and close tags
	private static $LT_OPEN =
		array(
			1	=>	"<ol>",
			2	=>	"<ul>"
		);

	private static $LT_CLOSE =
		array(
			1	=>	"</ol>",
			2	=>	"</ul>"
		);

	// constants for defining preformatted code state
	/*const 0	=	0;
	const 1		=	1;*/

	/*
	 * These rules contain the conversion from Wiki text to HTML
	 * described as regular expressions. The first part matches
	 * source text, the second part rewrites it as HTML.
	 */	 
	private static $RULES =
		array(
			'/^= (.*) =$/'			=>	'<h1>\1</h1>',
			'/^== (.*) ==$/'		=>	'<h2>\1</h2>',
			'/^=== (.*) ===$/'		=>	'<h3>\1</h3>',
			'/^==== (.*) ====$/'	=>	'<h4>\1</h4>',
			'/^===== (.*) =====$/'	=>	'<h5>\1</h5>',
			'/^====== (.*) ======$/'=> '<h6>\1</h6>',
			'/\[\[(.*?)\]\]/'		=>	'<span class="keys">\1</span>',
			'/^([ ]+)1 (.+)$/'		=>	'<li>\2</li>',
			'/^([ ]+)\* (.+)$/'		=>	'<li>\2</li>',
			//'/\*(.+?)\*/'			=>	'<em>\1</em>',
			//"/'''(.+?)'''/"			=>	'<b>\1</b>',
			"/\*\*(.+?)\*\*/"			=>	'<b>\1</b>',
			//"/''(.+?)''/"			=>	'<i>\1</i>',
			"/'(.+?)'/"			=>	'&#60;\1&gt;',
			"/\/\/(.+?)\/\//"			=>	'<i>\1</i>',
			"/__(.+?)__/"			=>	'<u>\1</u>',
			"/\[(.*?)\s(.*?)\]/"		=>	'<a href="$1">$2</a>',
			'/\[(.*?)\]/'			=>	'<a href="$1">$1</a>',
			'/`(.+?)`/'				=>	'<tt>\1</tt>',
			'/^----$/'				=>	'<hr />'
		);

	private static $URL =
		array(
			"/\[(.+?) (.+?)\]/",
			"/\[(.+?)\]/",
		);


	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}


	/**
	 * Converts a Wiki text input string to HTML.
	 * 
	 * @param	array	$input	The array of strings containing Wiki
	 * 				text markup.
	 * @return	array	An array of strings containing the output
	 * 			in HTML.	
	 */
	public function convertWikiTextToHTML($input) {

		$router = $this->container->get('router');

		// output array
		$output = array();

		// reset initial list states
		$liststate = 0;
		$listdepth = 1;
		$prevliststate = $liststate;
		$prevlistdepth = $listdepth;
		
		// preformatted code state
		$codestate = 0;

		// loop through the input
		foreach($input as $in) {
			// read, htmlify and right-trim each input line
			$in = htmlspecialchars(rtrim($in));
			$out = $in;


			// match against Wiki text to HTML rules
			foreach(self::$RULES as $pattern => $replacement){
/*-------------------Вычисление Битой ссылки----------------------------------*/
/* Не могу подставить [] в регулярное выражение выдает ошибку */
/*if($pattern == "/\[(.*?)\]/"){
	if( preg_match_all( $pattern, $out, $m ) ){
print  $out . '<br><pre>';
print_r($m);
print'</pre>';
		foreach( $m[1] as $link ){
			$url = strstr($link, ' ', true);
			$linkName = strstr($link, ' ');

			if( $url = '' ){
				$url = $link;
				$linkName = $link;
			}

			if(self::urlValid( $url ) == '0'){
				$url = $router->generate('wiki_add', array(), true);
			}
echo $out . '<br>';
			$link = htmlspecialchars( 'http://testrepairlive.ru/app_dev.php/wiki/dvuetazhnyye/add/' );
			$out = preg_replace( ' ' , "<a href='" . $url . "'>" . $linkName . "</a>", $out );
			
		}
	}
}
else*/
/*-------------------/Вычисление Битой ссылки----------------------------------*/
	$out = preg_replace($pattern, $replacement, $out);
			}

			// determine list state based on leftmost character
			$prevliststate = $liststate;
			$prevlistdepth = $listdepth;
			switch(substr(ltrim($in), 0, 1)) {
				case '1':
					$liststate = 1;
					$listdepth = strpos($in, '1');
					break;
				case '*':
					$liststate = 2;
					$listdepth = strpos($in, '*');
					break;
				default:
					$liststate = 0;
					$listdepth = 1;
					break;
			}

			// check if list state has changed
			if($liststate != $prevliststate) {
				// close old list
				if(0 != $prevliststate) {
					$output[] =
						self::$LT_CLOSE[$prevliststate];
				}
				
				// start new list
				if(0 != $liststate) {
					$output[] = self::$LT_OPEN[$liststate];
				}
			}
			
			// check if list depth has changed
			if ($listdepth != $prevlistdepth) {
				// calculate the depth difference
				$depthdiff = abs($listdepth - $prevlistdepth);

				// open or close tags based on difference
				if($listdepth > $prevlistdepth) {
					for($i = 0;
						$i < $depthdiff;
						$i++)
					{
						$output[] =
							self::$LT_OPEN[$liststate];
					}
				} else {
					for($i = 0;
						$i < $depthdiff;
						$i++)
					{
						$output[] =
							self::$LT_CLOSE[$prevliststate];
					}
				}
			}
			
			// determine output format
			if('' == $in) {
			} else if ('{{{' == trim($in)) {
				$output[] = '<p><pre><code>';
				$codestate = 1;
			} else if ('}}}' == trim($in)) {
				$output[] = '</code></pre></p>';
				$codestate = 0;
			} else if (
				$in[0] != '=' &&
				$in[0] != ' ' &&
				$in[0] != '-')
			{
				// only output paragraphs when not in code
				if(0 == $codestate) {
					$output[] = '<p>';
				}

				$output[] = $out;

				// only output paragraphs when not in code
				if(0 == $codestate) {
					$output[] = '</p>';
				}
			} else {
				$output[] = $out;
			}
		}

		// return the output
		return $output;
	}

	/**
	 * Converts an input stream to HTML.
	 * 
	 * @param	stream	$input	The input stream containing lines
	 * 				of Wiki text markup.
	 * @return	array	An array of strings containing the output
	 * 			in HTML.
	 */
	public static function convertWikiTextStreamToHTML($stream) {
		// input buffer
		$input = array();
		
		// loop through the stream
		while(!feof($stream)) {
			$input[] = fgets($stream);
		}

		// convert Wiki text to HTML and return result
		return self::convertWikiTextToHTML($input);
	}

	public static function urlValid(&$url)
	{
		$file_headers = @get_headers($url);
		if ($file_headers === false)
			return '0'; // when server not found

		foreach( $file_headers as $header ){ // parse all headers:
			// corrects $url when 301/302 redirect(s) lead(s) to 200:
			if(preg_match("/^Location: (http.+)$/",$header,$m))
				$url=$m[1]; 
			// grabs the last $header $code, in case of redirect(s):
			if(preg_match("/^HTTP.+\s(\d\d\d)\s/",$header,$m))
				$code=$m[1]; 
		} // End foreach...
		if($code==200)
			return '1'; // $code 200 == all OK
		else
			return '0'; // All else has failed, so this must be a bad link
	} // End function url_exists
}
?>