<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Top10\CabinetBundle\Entity\Wiki
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class wiki
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
	 * @ORM\OneToMany(targetEntity="Wiki", mappedBy="parent", cascade={"persist", "remove"})
	 */
	private $children;

	/**
	 * @ORM\ManyToOne(targetEntity="Wiki", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")	 
	 */
	protected $parent;

	/**
	* @var \Integer
	 * для формы
	*/
	public $parentint;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

	/**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=50)
     */
    private $code;


	/**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255)
    */
    private $image;


	/**
     * @var text $content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
	private $content;

    public function __construct()
    {
    	$this->children = new ArrayCollection();
    	// your own logic
    }

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

	public function getChildren()
    {
        return $this->children;
    }

	/**
	 * Set parent
	 *
	 * @param Top10\CabinetBundle\Entity\Wiki $parent
	 * @return Wiki
	 */
	public function setParent( Wiki $parent = null )
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Get parent
	 *
	 * @return Top10\CabinetBundle\Entity\Wiki 
	 */
	public function getParent()
	{
		return $this->parent;
	}

    /**
     * Set name
     *
     * @param string $name
     * @return wiki
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }


	/**
     * Set code
     *
     * @param string $code
     * @return wiki
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

	/**
     * Set image
     *
     * @param string $image
     * @return wiki
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }


	/**
     * Set content
     *
     * @param text $content
     * @return wiki
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return text 
     */
    public function getContent()
    {
        return $this->content;
    }
}